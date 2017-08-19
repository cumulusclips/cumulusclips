<?php

// Startup application
include_once(dirname(__FILE__) . '/bootstrap.php');

// Validate CLI parameters passed to script
$arguments = getopt('', array('video:', 'import::'));
if (!$arguments || !preg_match('/^[0-9]+$/', $arguments['video'])) exit();

// Validate provided import job
if (!empty($arguments['import'])) {
    if (file_exists(UPLOAD_PATH . '/temp/import-' . $arguments['import'])) {
        $importJobId = $arguments['import'];
    } else {
        exit('An invalid import job was passed to the video encoder.');
    }
} else {
    $importJobId = null;
}

// Establish page variables, objects, arrays, etc
$video_id = $arguments['video'];
$ffmpegPath = Settings::get('ffmpeg');
$qtFaststartPath = Settings::get('qtfaststart');
$videoMapper = new VideoMapper();
$videoService = new \VideoService();

// Update any failed videos that are still marked processing
$videoService->updateFailedVideos();

// Set MySQL wait_timeout to 10 hours to prevent 'MySQL server has gone away' errors
$db->query("SET @@session.wait_timeout=36000");

// Debug Log
if ($config->debugConversion) {
    App::log(CONVERSION_LOG, "\n\n// Converter Called...");
    App::log(CONVERSION_LOG, "Values passed to encoder:\n" . print_r ($arguments, TRUE));
}

try {

    /////////////////////////////////////////////////////////////
    //                        STEP 1                           //
    //       Check Permissions on Transcoding Binaries         //
    /////////////////////////////////////////////////////////////

    // Debug Log
    $config->debugConversion ? App::log(CONVERSION_LOG, "\nChecking FFMPEG permissions...") : null;
    if (strpos($ffmpegPath, DOC_ROOT) !== false && Filesystem::getPermissions($ffmpegPath) != '0777') {
        try {
            Filesystem::setPermissions($ffmpegPath, 0777);
        } catch (Exception $e) {
            throw new Exception("Unable to update permissions for FFMPEG. Please make sure it ($ffmpegPath) has 777 executeable permissions.\n\nAdditional information: " . $e->getMessage());
        }
    }

    // Debug Log
    $config->debugConversion ? App::log(CONVERSION_LOG, "\nChecking qt-faststart permissions...") : null;
    if (strpos($qtFaststartPath, DOC_ROOT) !== false && Filesystem::getPermissions($qtFaststartPath) != '0777') {
        try {
            Filesystem::setPermissions($qtFaststartPath, 0777);
        } catch (Exception $e) {
            throw new Exception("Unable to update permissions for qt-faststart. Please make sure it ($qtFaststartPath) has 777 executeable permissions.\n\nAdditional information: " . $e->getMessage());
        }
    }









    /////////////////////////////////////////////////////////////
    //                        STEP 2                           //
    //               Validate Requested Video                  //
    /////////////////////////////////////////////////////////////

    // Debug Log
    $config->debugConversion ? App::log(CONVERSION_LOG, 'Validating requested video...') : null;

    // Validate requested video
    $video = $videoMapper->getVideoByCustom(array('video_id' => $video_id, 'status' => VideoMapper::PENDING_CONVERSION));
    if (!$video) throw new Exception("An invalid video was passed to the video encoder.");

    // Debug Log
    $config->debugConversion ? App::log(CONVERSION_LOG, 'Establishing variables...') : null;

    // Retrieve video information
    $video->status = VideoMapper::PROCESSING;
    $video->jobId = posix_getpid();
    $videoMapper->save($video);
    $debugLog = LOG . '/' . $video->filename . '.log';
    $rawVideo = UPLOAD_PATH . '/temp/' . $video->filename . '.' . $video->originalExtension;
    $h264TempFilePath = UPLOAD_PATH . '/h264/' . $video->filename . '_temp.mp4';
    $h264FilePath = UPLOAD_PATH . '/h264/' . $video->filename . '.mp4';
    $theoraFilePath = UPLOAD_PATH . '/theora/' . $video->filename . '.ogg';
    $webmFilePath = UPLOAD_PATH . '/webm/' . $video->filename . '.webm';
    $mobileTempFilePath = UPLOAD_PATH . '/mobile/' . $video->filename . '_temp.mp4';
    $mobileFilePath = UPLOAD_PATH . '/mobile/' . $video->filename . '.mp4';
    $thumb = UPLOAD_PATH . '/thumbs/' . $video->filename . '.jpg';

    // Debug Log
    $config->debugConversion ? App::log(CONVERSION_LOG, 'Verifying raw video exists...') : null;

    // Verify Raw Video Exists
    if (!file_exists ($rawVideo)) throw new Exception("The raw video file does not exists. The id of the video is: $video->videoId");

    // Debug Log
    $config->debugConversion ? App::log(CONVERSION_LOG, 'Verifying raw video was valid size...') : null;

    // Verify Raw Video has valid file size
    // (Greater than min. 5KB, anything smaller is probably corrupted
    if (!filesize ($rawVideo) > 1024*5) throw new Exception("The raw video file is not a valid filesize. The id of the video is: $video->videoId");









    /////////////////////////////////////////////////////////////
    //                        STEP 3                           //
    //              Encode raw video to H.264                  //
    /////////////////////////////////////////////////////////////

    // Debug Log
    $config->debugConversion ? App::log(CONVERSION_LOG, "\nPreparing for: H.264 Encoding...") : null;

    // Encode raw video to H.264
    $h264Command = "$ffmpegPath -i $rawVideo " . Settings::get('h264_encoding_options') . " $h264TempFilePath >> $debugLog 2>&1";

    // Debug Log
    $logMessage = "\n\n\n\n==================================================================\n";
    $logMessage .= "H.264 ENCODING\n";
    $logMessage .= "==================================================================\n\n";
    $logMessage .= "H.264 Encoding Command: $h264Command\n\n";
    $logMessage .= "H.264 Encoding Output:\n\n";
    $config->debugConversion ? App::log(CONVERSION_LOG, 'H.264 Encoding Command: ' . $h264Command) : null;
    App::log($debugLog, $logMessage);

    // Execute H.264 encoding command
    exec($h264Command);

    // Debug Log
    $config->debugConversion ? App::log(CONVERSION_LOG, 'Verifying H.264 video was created...') : null;

    // Verify temp H.264 video was created successfully
    if (!file_exists($h264TempFilePath) || filesize($h264TempFilePath) < 1024*5) {
        throw new Exception("The temp H.264 file was not created. The id of the video is: $video->videoId");
    }









    /////////////////////////////////////////////////////////////
    //                        STEP 4                           //
    //            Shift Moov atom on H.264 video               //
    /////////////////////////////////////////////////////////////

    // Debug Log
    $config->debugConversion ? App::log(CONVERSION_LOG, "\nShifting moov atom on H.264 video...") : null;

    // Prepare shift moov atom command
    $h264ShiftMoovAtomCommand = "$qtFaststartPath $h264TempFilePath $h264FilePath >> $debugLog 2>&1";

    // Debug Log
    $logMessage = "\n\n\n\n==================================================================\n";
    $logMessage .= "H.264 SHIFT MOOV ATOM\n";
    $logMessage .= "==================================================================\n\n";
    $logMessage .= "H.264 Shift Moov Atom Command: $h264ShiftMoovAtomCommand\n\n";
    $logMessage .= "H.264 Shift Moov Atom Output:\n\n";
    $config->debugConversion ? App::log(CONVERSION_LOG, 'H.264 Shift Moov Atom Command: ' . $h264ShiftMoovAtomCommand) : null;
    App::log($debugLog, $logMessage);

    // Execute shift moov atom command
    exec($h264ShiftMoovAtomCommand);

    // Debug Log
    $config->debugConversion ? App::log(CONVERSION_LOG, 'Verifying final H.264 file was created...') : null;

    // Verify H.264 video was created successfully
    if (!file_exists($h264FilePath) || filesize($h264FilePath) < 1024*5) {
        throw new Exception("The final H.264 file was not created. The id of the video is: $video->videoId");
    }









    /////////////////////////////////////////////////////////////
    //                        STEP 5                           //
    //               Encode raw video to WebM                  //
    /////////////////////////////////////////////////////////////

    $webmEncodingEnabled = (Settings::get('webm_encoding_enabled') == '1') ? true : false;
    $webmEncodingOptions = Settings::get('webm_encoding_options');
    if ($webmEncodingEnabled) {
        // Debug Log
        $config->debugConversion ? App::log(CONVERSION_LOG, "\nPreparing for: WebM Encoding...") : null;

        // Encode raw video to WebM
        $webmCommand = "$ffmpegPath -i $rawVideo " . $webmEncodingOptions . " $webmFilePath >> $debugLog 2>&1";

        // Debug Log
        $logMessage = "\n\n\n\n==================================================================\n";
        $logMessage .= "WebM ENCODING\n";
        $logMessage .= "==================================================================\n\n";
        $logMessage .= "WebM Encoding Command: $webmCommand\n\n";
        $logMessage .= "WebM Encoding Output:\n\n";
        $config->debugConversion ? App::log(CONVERSION_LOG, 'WebM Encoding Command: ' . $webmCommand) : null;
        App::log($debugLog, $logMessage);

        // Execute WebM encoding command
        exec($webmCommand);

        // Debug Log
        $config->debugConversion ? App::log(CONVERSION_LOG, 'Verifying WebM video was created...') : null;

        // Verify temp WebM video was created successfully
        if (!file_exists($webmFilePath) || filesize($webmFilePath) < 1024*5) {
            throw new Exception("The WebM file was not created. The id of the video is: $video->videoId");
        }
    }









    /////////////////////////////////////////////////////////////
    //                        STEP 6                           //
    //              Encode raw video to Theora                 //
    /////////////////////////////////////////////////////////////

    $theoraEncodingEnabled = (Settings::get('theora_encoding_enabled') == '1') ? true : false;
    $theoraEncodingOptions = Settings::get('theora_encoding_options');
    if ($theoraEncodingEnabled) {
        // Debug Log
        $config->debugConversion ? App::log(CONVERSION_LOG, "\nPreparing for: Theora Encoding...") : null;

        // Encode raw video to Theora
        $theoraCommand = "$ffmpegPath -i $rawVideo " . $theoraEncodingOptions . " $theoraFilePath >> $debugLog 2>&1";

        // Debug Log
        $logMessage = "\n\n\n\n==================================================================\n";
        $logMessage .= "Theora ENCODING\n";
        $logMessage .= "==================================================================\n\n";
        $logMessage .= "Theora Encoding Command: $theoraCommand\n\n";
        $logMessage .= "Theora Encoding Output:\n\n";
        $config->debugConversion ? App::log(CONVERSION_LOG, 'Theora Encoding Command: ' . $theoraCommand) : null;
        App::log($debugLog, $logMessage);

        // Execute Theora encoding command
        exec($theoraCommand);

        // Debug Log
        $config->debugConversion ? App::log(CONVERSION_LOG, 'Verifying Theora video was created...') : null;

        // Verify temp Theora video was created successfully
        if (!file_exists($theoraFilePath) || filesize($theoraFilePath) < 1024*5) {
            throw new Exception("The Theora file was not created. The id of the video is: $video->videoId");
        }
    }









    /////////////////////////////////////////////////////////////
    //                        STEP 7                           //
    //              Encode raw video to Mobile                 //
    /////////////////////////////////////////////////////////////

    $mobileEncodingEnabled = (Settings::get('mobile_encoding_enabled') == '1') ? true : false;
    $mobileEncodingOptions = Settings::get('mobile_encoding_options');
    if ($mobileEncodingEnabled) {
        // Debug Log
        $config->debugConversion ? App::log(CONVERSION_LOG, "\nPreparing for: Mobile Encoding...") : null;

        // Encode raw video to Mobile
        $mobileCommand = "$ffmpegPath -i $rawVideo " . $mobileEncodingOptions . " $mobileTempFilePath >> $debugLog 2>&1";

        // Debug Log
        $logMessage = "\n\n\n\n==================================================================\n";
        $logMessage .= "Mobile ENCODING\n";
        $logMessage .= "==================================================================\n\n";
        $logMessage .= "Mobile Encoding Command: $mobileCommand\n\n";
        $logMessage .= "Mobile Encoding Output:\n\n";
        $config->debugConversion ? App::log(CONVERSION_LOG, 'Mobile Encoding Command: ' . $mobileCommand) : null;
        App::log($debugLog, $logMessage);

        // Execute Mobile encoding command
        exec($mobileCommand);

        // Debug Log
        $config->debugConversion ? App::log(CONVERSION_LOG, 'Verifying Mobile video was created...') : null;

        // Verify temp Mobile video was created successfully
        if (!file_exists($mobileTempFilePath) || filesize($mobileTempFilePath) < 1024*5) {
            throw new Exception("The Mobile file was not created. The id of the video is: $video->videoId");
        }









        /////////////////////////////////////////////////////////////
        //                        STEP 8                           //
        //            Shift Moov atom on Mobile video              //
        /////////////////////////////////////////////////////////////

        // Debug Log
        $config->debugConversion ? App::log(CONVERSION_LOG, "\nShifting moov atom on Mobile video...") : null;

        // Execute Faststart Command
        $faststartCommand = "$qtFaststartPath $mobileTempFilePath $mobileFilePath >> $debugLog 2>&1";

        // Debug Log
        $logMessage = "\n\n\n\n==================================================================\n";
        $logMessage .= "FASTSTART\n";
        $logMessage .= "==================================================================\n\n";
        $logMessage .= "Faststart Command: $faststartCommand";
        $logMessage .= "Faststart Output:\n\n";
        $config->debugConversion ? App::log(CONVERSION_LOG, 'Faststart Command: ' . $faststartCommand) : null;
        App::log($debugLog, $logMessage);

        // Execute Faststart command
        exec($faststartCommand);

        // Debug Log
        $config->debugConversion ? App::log(CONVERSION_LOG, 'Verifying final Mobile file was created...') : null;

        // Verify Mobile video was created successfully
        if (!file_exists($mobileFilePath) || filesize($mobileFilePath) < 1024*5) throw new Exception("The final Mobile file was not created. The id of the video is: $video->videoId");
    }









    /////////////////////////////////////////////////////////////
    //                        STEP 9                           //
    //                  Get Video Duration                     //
    /////////////////////////////////////////////////////////////

    // Debug Log
    $config->debugConversion ? App::log(CONVERSION_LOG, "\nRetrieving video duration...") : null;

    // Retrieve duration of raw video file.
    $durationCommand = "$ffmpegPath -i $rawVideo 2>&1 | grep Duration:";
    exec($durationCommand, $durationResults);

    // Debug Log
    $config->debugConversion ? App::log(CONVERSION_LOG, "Duration command results:\n" . print_r ($durationResults, TRUE)) : null;

    $durationResultsCleaned = preg_replace('/^\s*Duration:\s*/', '', $durationResults[0]);
    preg_match ('/^[0-9]{2}:[0-9]{2}:[0-9]{2}/', $durationResultsCleaned, $duration);
    $sec = Functions::durationToSeconds($duration[0]);

    // Debug Log
    $config->debugConversion ? App::log(CONVERSION_LOG, "Duration in Seconds: $sec") : null;

    // Calculate thumbnail position
    $thumbPosition = round ($sec / 2);

    // Debug Log
    $config->debugConversion ? App::log(CONVERSION_LOG, "Thumb Position: $thumbPosition") : null;









    /////////////////////////////////////////////////////////////
    //                        STEP 10                          //
    //                Create Thumbnail Image                   //
    /////////////////////////////////////////////////////////////

    // Debug Log
    $config->debugConversion ? App::log(CONVERSION_LOG, "\nPreparing to create video thumbnail...") : null;

    // Create video thumbnail image
    $thumbCommand = "$ffmpegPath -i $rawVideo -ss $thumbPosition " . Settings::get('thumb_encoding_options') . " $thumb >> $debugLog 2>&1";

    // Debug Log
    $logMessage = "\n\n\n\n==================================================================\n";
    $logMessage .= "THUMB CREATION\n";
    $logMessage .= "==================================================================\n\n";
    $logMessage .= "Thumb Creation Command: $thumbCommand";
    $logMessage .= "Thumb Creation Output:\n\n";
    $config->debugConversion ? App::log(CONVERSION_LOG, "Thumbnail Creation Command: " . $thumbCommand) : null;
    App::log($debugLog, $logMessage);

    exec($thumbCommand);    // Execute Thumb Creation Command

    // Debug Log
    $config->debugConversion ? App::log(CONVERSION_LOG, 'Verifying valid thumbnail was created...') : null;

    // Verify valid thumbnail was created
    if (!file_exists($thumb) || filesize($thumb) == 0) throw new Exception("The video thumbnail is invalid. The id of the video is: $video->videoId");









    /////////////////////////////////////////////////////////////
    //                        STEP 11                          //
    //               Update Video Information                  //
    /////////////////////////////////////////////////////////////

    // Debug Log
    $config->debugConversion ? App::log(CONVERSION_LOG, "\nUpdating video information...") : null;

    // Update database with new video status information
    $video->duration = Functions::formatDuration($duration[0]);
    $video->status = \VideoMapper::PENDING_APPROVAL;
    $video->jobId = null;
    $videoMapper->save($video);

    // Activate video
    $videoService->approve($video, 'activate');









    /////////////////////////////////////////////////////////////
    //                        STEP 12                          //
    //                 Notify Import Script                    //
    /////////////////////////////////////////////////////////////

    if ($importJobId) {

        // Debug Log
        $config->debugConversion ? App::log(CONVERSION_LOG, "\nNotifying import script of completed video...") : null;
        \ImportManager::executeImport($importJobId);
    }









    /////////////////////////////////////////////////////////////
    //                         STEP 13                         //
    //                        Clean up                         //
    /////////////////////////////////////////////////////////////

    try {
        // Debug Log
        $config->debugConversion ? App::log(CONVERSION_LOG, 'Deleting raw video...') : null;

        // Delete pre-faststart files
        Filesystem::delete($h264TempFilePath);
        if ($mobileEncodingEnabled) Filesystem::delete($mobileTempFilePath);

        // Delete original video
        if (Settings::get('keep_original_video') != '1') {
            Filesystem::delete($rawVideo);
        }

        // Delete encoding log files
        if ($config->debugConversion) {
            App::log(CONVERSION_LOG, "Video ID: $video->videoId, has completed processing!\n");
        } else {
            Filesystem::delete($debugLog);
        }
    } catch (Exception $e) {
        App::alert('Error During Video Encoding', $e->getMessage());
        App::log(CONVERSION_LOG, $e->getMessage());
    }

} catch (Exception $e) {

    // Update video status
    if ($video) {
        $video->status = \VideoMapper::FAILED;
        $video->jobId = null;
        $videoMapper->save($video);
    }

    // Notify import script of error
    if ($importJobId) {
        \ImportManager::executeImport($importJobId);
    }

    App::alert('Error During Video Encoding', $e->getMessage());
    App::log(CONVERSION_LOG, $e->getMessage());
    exit();
}