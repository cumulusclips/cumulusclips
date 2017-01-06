<?php

class ImportManager
{
    /**
     * @var string Status for import jobs that are in progress
     */
    const JOB_PROGRESS = 'progress';

    /**
     * @var string Status for import jobs that have completed
     */
    const JOB_COMPLETED = 'completed';

    /**
     * @var string Status for import jobs that have completed but contain failures
     */
    const JOB_COMPLETED_FAILURES = 'completed_failures';

    /**
     * @var string Import status for videos waiting to be imported
     */
    const VIDEO_QUEUED = 'queued';

    /**
     * @var string Import status for videos that are undergoing transcoding
     */
    const VIDEO_TRANSCODING = 'transcoding';

    /**
     * @var string Import status for videos that have been successfully imported
     */
    const VIDEO_COMPLETED = 'completed';

    /**
     * @var string Import status for videos that have failed to be imported
     */
    const VIDEO_FAILED = 'failed';

    /**
     * Creates a new video import
     *
     * @param \User $user The user to attribute the import to
     * @param string|null $metaDataFile Path to import meta data file
     * @return string Returns the job ID of the newly created import
     * @throws \Exception Thrown if no files were available for import
     * @throws \Exception Thrown if invalid video files are attempted to be imported
     */
    public static function createImport(\User $user, $metaDataFile = null)
    {
        // Load meta data from file if provided
        $importMetaData = ($metaDataFile) ? simplexml_load_file($metaDataFile) : null;

        // Count video files available for import
        if (!\Filesystem::isEmpty(UPLOAD_PATH . '/import')) {

            // Create manifest
            $manifest = (object) array(
                'dateCreated' => gmdate('F j, Y H:i:s'),
                'dateCompleted' => null,
                'userId' => $user->userId,
                'status' => static::JOB_PROGRESS,
                'current' => null,
                'videos' => array()
            );

            // Generate job id
            do {
                $jobId = \Functions::random(7);
            } while (file_exists(UPLOAD_PATH . '/temp/import-' . $jobId));

            // Create job directory
            $importDirectory = UPLOAD_PATH . '/temp/import-' . $jobId;
            \Filesystem::createDir($importDirectory);

            // Build manifest's video list
            $filesystemIterator = new FilesystemIterator(UPLOAD_PATH . '/import', FilesystemIterator::SKIP_DOTS);
            $config = \Registry::get('config');
            foreach ($filesystemIterator as $videoFile) {

                // Validate video file is allowed for import
                if (!in_array(\Functions::getExtension($videoFile), $config->acceptedVideoFormats)) {
                    throw new \Exception('Invalid video type encountered in import files: ' . $videoFile);
                }

                $manifest->videos[] = (object) array(
                    'file' => basename($videoFile),
                    'videoId' => null,
                    'status' => static::VIDEO_QUEUED,
                    'meta' => self::getMetaData(basename($videoFile), $importMetaData)
                );

                // Move videos to be imported to job directory
                \Filesystem::rename($videoFile, $importDirectory . '/' . basename($videoFile));
            }

            // Create job manifest file
            \Filesystem::create($importDirectory . '/import.manifest');
            \Filesystem::write($importDirectory . '/import.manifest', json_encode($manifest));

            // Begin import
            self::executeImport($jobId);

            return $jobId;

        } else {
            throw new \Exception('No files were found for import. Please make sure you uploaded the videos to be imported to the '
                . '/cc-content/uploads/import directory.');
        }
    }

    /**
     * Restarts an existing video import
     *
     * @param string $jobId ID of import job to being restarted
     * @return string Returns the job ID of the newly created import
     * @throws \Exception thrown if import job does not exist
     * @throws \Exception thrown if import job has not completed with failures
     */
    public static function restartImport($jobId)
    {
        // Verify import job exists
        if (!file_exists(UPLOAD_PATH . '/temp/import-' . $jobId)) {
            throw new \Exception('Import job does not exist');
        }

        $manifest = self::getManifest($jobId);

        // Verify import can be restarted
        if ($manifest->status !== \ImportManager::JOB_COMPLETED_FAILURES) {
            throw new \Exception('Only import jobs that previously had failures can be restarted');
        }

        // Mark import job's uncompleted videos as queued
        foreach ($manifest->videos as $video) {
            if (in_array($video->status, array(\ImportManager::VIDEO_FAILED, \ImportManager::VIDEO_TRANSCODING))) {
                $video->status = \ImportManager::VIDEO_QUEUED;
            }
        }

        // Update import's manifest
        $manifest->current = null;
        $manifest->status = static::JOB_PROGRESS;
        self::saveManifest($jobId, $manifest);

        // Begin import
        self::executeImport($jobId);
    }

    /**
     * Deletes import job and associated files from system
     *
     * @param string $jobId ID of import job to be deleted
     * @throws \Exception thrown if import job does not exist
     * @throws \Exception thrown if import job is in progress
     */
    public function removeImport($jobId)
    {
        // Verify import job exists
        if (!file_exists(UPLOAD_PATH . '/temp/import-' . $jobId)) {
            throw new \Exception('Import job does not exist');
        }

        $manifest = self::getManifest($jobId);

        // Verify import is not in progress
        if ($manifest->status === \ImportManager::JOB_PROGRESS) {
            throw new \Exception('Cannot remove import job that is in progress');
        }

        // Remove import job and log file
        \Filesystem::delete(UPLOAD_PATH . '/temp/import-' . $jobId);
        \Filesystem::delete(LOG . '/import-' . $jobId . '.log');
    }

    /**
     * Generates meta data for a given imported video
     *
     * @param string $videoFile Filename of the imported video
     * @param \SimpleXMLElement|null $metaData Meta data for the import
     * @return \stdClass Returns an object with the meta data for the video
     */
    protected static function getMetaData($videoFile, $importMetaData = null)
    {
        $metaDataVideo = null;
        $metaData = (object) array();
        $filename = pathinfo($videoFile, PATHINFO_FILENAME);

        if (isset($importMetaData->video)) {
            $videoList = $importMetaData->video;
        } else {
            $videoList = array();
        }

        foreach ($videoList as $video) {
            if (isset($video->filename) && trim($video->filename) == $videoFile) {
                $metaDataVideo = $video;
                break;
            }
        }

        // Set video's title
        if (!empty($metaDataVideo->title)) {
            $metaData->title = (string) $metaDataVideo->title;
        } else {
            $metaData->title = $filename;
        }

        // Set video's description
        if (!empty($metaDataVideo->description)) {
            $metaData->description = (string) $metaDataVideo->description;
        } else {
            $metaData->description = $filename;
        }

        // Set video's category
        if (!empty($metaDataVideo->category)) {
            $categoryMapper = new \CategoryMapper();
            $category = $categoryMapper->getCategoryByCustom(array('name' => $metaDataVideo->category));
            $metaData->category = ($category)
                ? $category->categoryId
                : self::getDefaultCategory()->categoryId;
        } else {
            $metaData->category = self::getDefaultCategory()->categoryId;
        }

        // Set video's tags
        if (!empty($metaDataVideo->tags->tag)) {
            $metaData->tags = array();
            foreach ($metaDataVideo->tags->tag as $tag) {
                $metaData->tags[] = (string) $tag;
            }
        } else {
            $metaData->tags = array($filename);
        }

        return $metaData;
    }

    /**
     * Retrieves the default category
     *
     * @return \Category Returns category
     */
    protected static function getDefaultCategory()
    {
        // Retrieve default category
        $categoryService = new \CategoryService();
        $categories = $categoryService->getCategories();
        return array_shift($categories);
    }

    /**
     * Retrieves the manifest for a given import job
     *
     * @param string $jobId Id of import job to load manifest for
     * @return \stdClass Returns manifest
     */
    public static function getManifest($jobId)
    {
        return json_decode(file_get_contents(
            UPLOAD_PATH . '/temp/import-' . $jobId . '/import.manifest'
        ));
    }

    /**
     * Saves manifest data for given import job
     *
     * @param string $jobId Id of import job to save manifest for
     * @param \stdClass $manifest Manifest data to be saved
     */
    public static function saveManifest($jobId, \stdClass $manifest)
    {
        \Filesystem::write(
            UPLOAD_PATH . '/temp/import-' . $jobId . '/import.manifest',
            json_encode($manifest),
            false
        );
    }

    /**
     * Retrieves import job associated with given video
     *
     * @param int $videoId ID of video to get import job for
     * @return string|null Returns import job ID of video, null if no associated import job was found
     */
    public static function getAssociatedImport($videoId)
    {
        // Cycle through existing import jobs
        foreach (glob(UPLOAD_PATH . '/temp/import-*') as $import) {

            // Load import job's manifest
            preg_match('/import\-([a-z0-9]+)$/i', $import, $matches);
            $importJobId = $matches[1];
            $manifest = self::getManifest($importJobId);

            // Cycle through import's videos to find matching video
            foreach ($manifest->videos as $video) {
                if ($video->videoId == $videoId) {
                    return $importJobId;
                }
            }
        }

        return null;
    }

    /**
     * Retrieves index of next video in import queue
     *
     * @param \stdClass $manifest Manifest of import job to retrieve next video for
     * @return int|boolean Returns index of next video in queue, boolean false if end of queue is reached
     */
    public static function getNextVideoInQueue($manifest)
    {
        $queuedVideos = array();
        foreach ($manifest->videos as $key => $video) {
            if ($video->status == static::VIDEO_QUEUED) {
                return $key;
            }
        }
        return false;
    }

    /**
     * Saves given video in the system
     *
     * @param \stdClass $importVideo Video information
     * @param int $userId ID to associate video to
     * @return \Video Returns newly created video
     */
    public function createVideo(\stdClass $importVideo, $userId)
    {
        $videoMapper = new \VideoMapper();
        $videoService = new \VideoService();

        $video = new \Video();
        $video->filename = $videoService->generateFilename();
        $video->originalExtension = pathinfo($importVideo->file, PATHINFO_EXTENSION);
        $video->title = $importVideo->meta->title;
        $video->description = $importVideo->meta->description;
        $video->tags = $importVideo->meta->tags;
        $video->userId = $userId;
        $video->status = \VideoMapper::PENDING_CONVERSION;
        $video->categoryId = $importVideo->meta->category;
        $videoId = $videoMapper->save($video);
        return $videoMapper->getVideoById($videoId);
    }

    /**
     * Executes transcoding script for given video
     *
     * @param int $videoId ID of video to begin transcoding
     * @param string $jobId ID of import job triggering the transcoding
     */
    public static function transcode($videoId, $jobId)
    {
        // Determine output location
        $cmdOutput = Registry::get('config')->debugConversion ? CONVERSION_LOG : '/dev/null';

        $command = 'nohup ' . Settings::get('php') . ' ' . DOC_ROOT . '/cc-core/system/encode.php --video="' . $videoId . '" --import="' . $jobId . '" >> ' .  $cmdOutput . ' 2>&1 &';
        exec($command);
    }

    /**
     * Executes import script
     *
     * @param string $jobId The ID of the job to execute the import script on
     * @throws \Exception Thrown if invalid action is requested for import script
     */
    public static function executeImport($jobId)
    {
        $importLog = LOG . '/import-' . $jobId . '.log';
        $command = 'nohup ' . Settings::get('php') . ' ' . DOC_ROOT . '/cc-core/system/bin/import.php --job="' . $jobId . '" >> ' .  $importLog . ' 2>&1 &';
        exec($command);
    }

    /**
     * Sends admin alert notifying them import job has completed
     *
     * @param string $jobId The ID of the import job that has completed
     */
    public function sendAlert($jobId)
    {
        // Check if admin alerts are turned on
        if (\Settings::get('alerts_imports') === '1') {

            $manifest = self::getManifest($jobId);
            $userMapper = new \UserMapper();
            $dateStart = new \DateTime($manifest->dateCreated, new \DateTimeZone('UTC'));
            $dateCompleted = new \DateTime($manifest->dateCompleted, new \DateTimeZone('UTC'));

            // Build message
            $subject = 'Video Import Complete';
            $body = 'Video import job ' . $jobId . ' has completed';
            $body .= ($manifest->status === static::JOB_COMPLETED_FAILURES) ? ' with failures.' : '.';
            $body .= "\n\n=======================================================\n";
            $body .= 'Started On: ' . \Functions::gmtToLocal($manifest->dateCreated, 'M d, Y g:i A T') . "\n";
            $body .= 'Duration: ' . \Functions::getTimeSince($dateStart, $dateCompleted) . "\n";
            $body .= 'Started By: ' . $userMapper->getUserById($manifest->userId)->username . "\n";
            $body .= "=======================================================";

            // Send alert
            App::alert($subject, $body);
        }
    }
}
