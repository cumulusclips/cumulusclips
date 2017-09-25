<?php

// Startup application
include_once(dirname(dirname(__FILE__)) . '/bootstrap.php');

// Check if transcoding is enabled
if (Settings::get('enable_encoding') != '1') exit('Video transcoding is disabled');

// Validate provided arguments
$arguments = getopt('', array('job:'));
if (
    empty($arguments)
    || !file_exists(UPLOAD_PATH . '/temp/import-' . $arguments['job'] . '/import.manifest')
) {
    exit('Invalid arguments passed to import');
}

// Establish page variables, objects, arrays, etc
$videoMapper = new \VideoMapper();
$jobId = $arguments['job'];
$importLog = LOG . '/import-' . $jobId . '.log';
App::log($importLog, '[' . date('Y-m-d H:i:s T') . '] Running Import Script for Job ID: ' . $jobId);
$manifest = \ImportManager::getManifest($jobId);

// Verify import job is in progress
App::log($importLog, '[' . date('Y-m-d H:i:s T') . '] Validating requested import job...');
if ($manifest->status !== \ImportManager::JOB_PROGRESS) {
    exit(sprintf('Cannot operate on import job "%s" that is not in progress', $jobId));
}

App::log($importLog, '[' . date('Y-m-d H:i:s T') . '] Checking if import job has an active video...');

// Check if import job has an active video
if ($manifest->current !== null) {

    App::log($importLog, '[' . date('Y-m-d H:i:s T') . '] Active video index: ' . $manifest->current);

    // Load current video from manifest and system
    $currentImportVideo = $manifest->videos[$manifest->current];
    $currentVideo = $videoMapper->getVideoById($currentImportVideo->videoId);

    // Determine status of current video
    App::log($importLog, '[' . date('Y-m-d H:i:s T') . '] Checking status of active video...');
    if (in_array($currentVideo->status, array(VideoMapper::APPROVED, VideoMapper::PENDING_APPROVAL))) {
        $currentImportVideo->status = \ImportManager::VIDEO_COMPLETED;
    } else if ($currentVideo->status == \VideoMapper::FAILED) {
        $currentImportVideo->status = \ImportManager::VIDEO_FAILED;
    } else {
        exit('Invalid next request on import that is in progress.');
    }

    App::log($importLog, '[' . date('Y-m-d H:i:s T') . '] Updating current video with new status (' . $currentImportVideo->status . ') in manifest...');
    \ImportManager::saveManifest($jobId, $manifest);
}


// Determine whether to continue to next queued video or if end of import has been reached
App::log($importLog, '[' . date('Y-m-d H:i:s T') . '] Checking if import job has more queued videos...');
$nextImportVideoKey = \ImportManager::getNextVideoInQueue($manifest);
if ($nextImportVideoKey !== false) {

    App::log($importLog, '[' . date('Y-m-d H:i:s T') . '] Moving to next queued video (Index #' . $nextImportVideoKey . ')...');

    // Grab new active video
    $nextImportVideo = $manifest->videos[$nextImportVideoKey];

    // Create system video record
    App::log($importLog, '[' . date('Y-m-d H:i:s T') . '] Creating new system video record...');
    $newVideo = \ImportManager::createVideo($nextImportVideo, $manifest->userId);
    App::log($importLog, '[' . date('Y-m-d H:i:s T') . '] Video ID: ' . $newVideo->videoId);

    // Copy imported video over to transcoding temp directory
    App::log($importLog, '[' . date('Y-m-d H:i:s T') . '] Copying imported video to transcoding temp directory...');
    \Filesystem::copy(
        UPLOAD_PATH . '/temp/import-' . $jobId . '/' . $nextImportVideo->file,
        UPLOAD_PATH . '/temp/' . $newVideo->filename . '.' . $newVideo->originalExtension
    );

    // Update manifest to next video
    App::log($importLog, '[' . date('Y-m-d H:i:s T') . '] Updating manifest with new active video information...');
    $nextImportVideo->status = \ImportManager::VIDEO_TRANSCODING;
    $nextImportVideo->videoId = $newVideo->videoId;
    $manifest->current = $nextImportVideoKey;
    \ImportManager::saveManifest($jobId, $manifest);

    // Start transcoding
    App::log($importLog, '[' . date('Y-m-d H:i:s T') . '] Calling transcoder...');
    \ImportManager::transcode($newVideo->videoId, $jobId);

} else {

    App::log($importLog, '[' . date('Y-m-d H:i:s T') . '] No queued import videos found...');

    // Detect if import job had any failures
    App::log($importLog, '[' . date('Y-m-d H:i:s T') . '] Checking if any video imports failed...');
    $importJobHasFailures = false;
    foreach ($manifest->videos as $video) {
        if ($video->status == \ImportManager::VIDEO_FAILED) {
            $importJobHasFailures = true;
            break;
        }
    }

    // Mark import job as complete
    App::log($importLog, '[' . date('Y-m-d H:i:s T') . '] Marking import job as complete...');
    $manifest->current = null;
    $manifest->dateCompleted = gmdate('F j, Y H:i:s');
    $manifest->status = ($importJobHasFailures) ? \ImportManager::JOB_COMPLETED_FAILURES : \ImportManager::JOB_COMPLETED;
    \ImportManager::saveManifest($jobId, $manifest);
    \ImportManager::sendAlert($jobId);
    App::log($importLog, '[' . date('Y-m-d H:i:s T') . '] Import Job ' . $jobId . ' Complete!');
}
