<?php

// Startup application
include_once (dirname (dirname (__FILE__)) . '/config/bootstrap.php');

// Establish page variables, objects, arrays, etc
if (!isset ($argv[1]) || !preg_match ('/--video=([0-9]+)$/i', $argv[1], $arg_matches)) exit();
$video_id = $arg_matches[1];
Plugin::Trigger ('encode.parse');
$ffmpeg_path = Settings::Get ('ffmpeg');
$qt_faststart_path = DOC_ROOT . '/cc-core/system/bin/qtfaststart';
$videoMapper = new VideoMapper();

// Set MySQL wait_timeout to 10 hours to prevent 'MySQL server has gone away' errors
$db->Query ("SET @@session.wait_timeout=36000");

// Debug Log
if ($config->debug_conversion) {
    App::Log (CONVERSION_LOG, "\n\n// Converter Called...");
    App::Log (CONVERSION_LOG, "Values passed to encoder:\n" . print_r ($argv, TRUE));
}

try {
    /////////////////////////////////////////////////////////////
    //                        STEP 1                           //
    //               Validate Requested Video                  //
    /////////////////////////////////////////////////////////////

    // Debug Log
    $config->debug_conversion ? App::Log (CONVERSION_LOG, 'Validating requested video...') : null;

    // Validate requested video
    
    $video = $videoMapper->getVideoByCustom(array('video_id' => $video_id, 'status' => VideoMapper::PENDING_CONVERSION));
    if (!$video) throw new Exception ("An invalid video was passed to the video encoder.");

    // Debug Log
    $config->debug_conversion ? App::Log (CONVERSION_LOG, 'Establishing variables...') : null;

    // Retrieve video information
    $video->status = VideoMapper::PROCESSING;
    $videoMapper->save($video);
    $debug_log = LOG . '/' . $video->filename . '.log';
    $raw_video = UPLOAD_PATH . '/temp/' . $video->filename . '.' . $video->originalExtension;
    $h264TempFilePath = UPLOAD_PATH . '/h264/' . $video->filename . '_temp.mp4';
    $h264FilePath = UPLOAD_PATH . '/h264/' . $video->filename . '.mp4';
    $theoraFilePath = UPLOAD_PATH . '/theora/' . $video->filename . '.ogv';
    $vp8FilePath = UPLOAD_PATH . '/vp8/' . $video->filename . '.webm';
    $mobile_temp = UPLOAD_PATH . '/mobile/' . $video->filename . '_temp.mp4';
    $mobile = UPLOAD_PATH . '/mobile/' . $video->filename . '.mp4';
    $thumb = UPLOAD_PATH . '/thumbs/' . $video->filename . '.jpg';
    Plugin::Trigger ('encode.load_video');

    // Debug Log
    $config->debug_conversion ? App::Log (CONVERSION_LOG, 'Verifying raw video exists...') : null;

    // Verify Raw Video Exists
    if (!file_exists ($raw_video)) throw new Exception ("The raw video file does not exists. The id of the video is: $video->videoId");

    // Debug Log
    $config->debug_conversion ? App::Log (CONVERSION_LOG, 'Verifying raw video was valid size...') : null;

    // Verify Raw Video has valid file size
    // (Greater than min. 5KB, anything smaller is probably corrupted
    if (!filesize ($raw_video) > 1024*5) throw new Exception ("The raw video file is not a valid filesize. The id of the video is: $video->videoId");









    /////////////////////////////////////////////////////////////
    //                        STEP 2                           //
    //              Encode raw video to H.264                  //
    /////////////////////////////////////////////////////////////

    // Debug Log
    $config->debug_conversion ? App::Log(CONVERSION_LOG, "\nPreparing for: H.264 Encoding...") : null;

    // Encode raw video to H.264
    $h264Command = "$ffmpeg_path -i $raw_video " . Settings::Get('h264EncodingOptions') . " $h264TempFilePath >> $debug_log 2>&1";
    $h264Command = Plugin::triggerFilter('encode.before_h264_encode', $h264Command);

    // Debug Log
    $log_msg = "\n\n\n\n==================================================================\n";
    $log_msg .= "H.264 ENCODING\n";
    $log_msg .= "==================================================================\n\n";
    $log_msg .= "H.264 Encoding Command: $h264Command\n\n";
    $log_msg .= "H.264 Encoding Output:\n\n";
    $config->debug_conversion ? App::Log(CONVERSION_LOG, 'H.264 Encoding Command: ' . $h264Command) : null;
    App::Log($debug_log, $log_msg);

    // Execute H.264 encoding command
    exec($h264Command);
    Plugin::triggerEvent('encode.h264_encode');

    // Debug Log
    $config->debug_conversion ? App::Log(CONVERSION_LOG, 'Verifying H.264 video was created...') : null;

    // Verify temp H.264 video was created successfully
    if (!file_exists($h264TempFilePath) || filesize($h264TempFilePath) < 1024*5) {
        throw new Exception("The temp H.264 file was not created. The id of the video is: $video->videoId");
    }









    /////////////////////////////////////////////////////////////
    //                        STEP 3                           //
    //            Shift Moov atom on H.264 video               //
    /////////////////////////////////////////////////////////////

    // Debug Log
    $config->debug_conversion ? App::Log(CONVERSION_LOG, "\nChecking qt-faststart permissions...") : null;

    if ((string) substr(sprintf('%o', fileperms($qt_faststart_path)), -4) != '0777') {
        try {
            Filesystem::setPermissions($qt_faststart_path, 0777);
        } catch (Exception $e) {
            throw new Exception("Unable to update permissions for qt-faststart. Please make sure it ($qt_faststart_path) has 777 executeable permissions.\n\nAdditional information: " . $e->getMessage());
        }
    }

    // Debug Log
    $config->debug_conversion ? App::Log(CONVERSION_LOG, "\nShifting moov atom on H.264 video...") : null;

    // Prepare shift moov atom command
    $h264ShiftMoovAtomCommand = "$qt_faststart_path $h264TempFilePath $h264FilePath >> $debug_log 2>&1";
    Plugin::triggerFilter('encode.before_h264_shift_moov_atom', $h264ShiftMoovAtomCommand);

    // Debug Log
    $log_msg = "\n\n\n\n==================================================================\n";
    $log_msg .= "H.264 SHIFT MOOV ATOM\n";
    $log_msg .= "==================================================================\n\n";
    $log_msg .= "H.264 Shift Moov Atom Command: $h264ShiftMoovAtomCommand\n\n";
    $log_msg .= "H.264 Shift Moov Atom Output:\n\n";
    $config->debug_conversion ? App::Log(CONVERSION_LOG, 'H.264 Shift Moov Atom Command: ' . $h264ShiftMoovAtomCommand) : null;
    App::Log($debug_log, $log_msg);

    // Execute shift moov atom command
    exec($h264ShiftMoovAtomCommand);
    Plugin::triggerEvent('encode.h264_shift_moov_atom');

    // Debug Log
    $config->debug_conversion ? App::Log(CONVERSION_LOG, 'Verifying final H.264 file was created...') : null;

    // Verify H.264 video was created successfully
    if (!file_exists($h264FilePath) || filesize($h264FilePath) < 1024*5) {
        throw new Exception("The final H.264 file was not created. The id of the video is: $video->videoId");
    }









    /////////////////////////////////////////////////////////////
    //                        STEP 4                           //
    //              Encode raw video to Theora                 //
    /////////////////////////////////////////////////////////////

    // Debug Log
    $config->debug_conversion ? App::Log(CONVERSION_LOG, "\nPreparing for: Theora Encoding...") : null;

    // Encode raw video to Theora
    $theoraCommand = "$ffmpeg_path -i $raw_video " . Settings::Get('theoraEncodingOptions') . " $theoraFilePath >> $debug_log 2>&1";
    $theoraCommand = Plugin::triggerFilter('encode.before_theora_encode', $theoraCommand);

    // Debug Log
    $log_msg = "\n\n\n\n==================================================================\n";
    $log_msg .= "Theora ENCODING\n";
    $log_msg .= "==================================================================\n\n";
    $log_msg .= "Theora Encoding Command: $theoraCommand\n\n";
    $log_msg .= "Theora Encoding Output:\n\n";
    $config->debug_conversion ? App::Log(CONVERSION_LOG, 'Theora Encoding Command: ' . $theoraCommand) : null;
    App::Log($debug_log, $log_msg);

    // Execute Theora encoding command
    exec($theoraCommand);
    Plugin::triggerEvent('encode.theora_encode');

    // Debug Log
    $config->debug_conversion ? App::Log(CONVERSION_LOG, 'Verifying Theora video was created...') : null;

    // Verify temp Theora video was created successfully
    if (!file_exists($theoraFilePath) || filesize($theoraFilePath) < 1024*5) {
        throw new Exception("The Theora file was not created. The id of the video is: $video->videoId");
    }









    /////////////////////////////////////////////////////////////
    //                        STEP 5                           //
    //               Encode raw video to VP8                   //
    /////////////////////////////////////////////////////////////

    $vp8Settings = json_decode(Settings::Get('vp8Options'));
    if ($vp8Settings->enabled) {
        // Debug Log
        $config->debug_conversion ? App::Log(CONVERSION_LOG, "\nPreparing for: VP8 Encoding...") : null;

        // Encode raw video to VP8
        $vp8Command = "$ffmpeg_path -i $raw_video " . $vp8Settings->options . " $vp8FilePath >> $debug_log 2>&1";
        $vp8Command = Plugin::triggerFilter('encode.before_vp8_encode', $vp8Command);

        // Debug Log
        $log_msg = "\n\n\n\n==================================================================\n";
        $log_msg .= "VP8 ENCODING\n";
        $log_msg .= "==================================================================\n\n";
        $log_msg .= "VP8 Encoding Command: $vp8Command\n\n";
        $log_msg .= "VP8 Encoding Output:\n\n";
        $config->debug_conversion ? App::Log(CONVERSION_LOG, 'VP8 Encoding Command: ' . $vp8Command) : null;
        App::Log($debug_log, $log_msg);

        // Execute VP8 encoding command
        exec($vp8Command);
        Plugin::triggerEvent('encode.vp8_encode');

        // Debug Log
        $config->debug_conversion ? App::Log(CONVERSION_LOG, 'Verifying VP8 video was created...') : null;

        // Verify temp VP8 video was created successfully
        if (!file_exists($vp8FilePath) || filesize($vp8FilePath) < 1024*5) {
            throw new Exception("The VP8 file was not created. The id of the video is: $video->videoId");
        }
    }









    /////////////////////////////////////////////////////////////
    //                        STEP 6                           //
    //              Encode raw video to Mobile                 //
    /////////////////////////////////////////////////////////////

    // Debug Log
    $config->debug_conversion ? App::Log (CONVERSION_LOG, "\nPreparing for: Mobile Encoding...") : null;

    // Encode raw video to Mobile
    $mobile_command = "$ffmpeg_path -i $raw_video " . Settings::Get('mobile_options') . " $mobile_temp >> $debug_log 2>&1";
    Plugin::Trigger ('encode.before_mobile_encode');

    // Debug Log
    $log_msg = "\n\n\n\n==================================================================\n";
    $log_msg .= "MOBILE ENCODING\n";
    $log_msg .= "==================================================================\n\n";
    $log_msg .= "Mobile Encoding Command: $mobile_command\n\n";
    $log_msg .= "Mobile Encoding Output:\n\n";
    $config->debug_conversion ? App::Log (CONVERSION_LOG, 'Mobile Encoding Command: ' . $mobile_command) : null;
    App::Log ($debug_log, $log_msg);

    // Execute Mobile Encoding Command
    exec ($mobile_command);
    Plugin::Trigger ('encode.mobile_encode');

    // Debug Log
    $config->debug_conversion ? App::Log (CONVERSION_LOG, 'Verifying temp Mobile file was created...') : null;

    // Verify temp Mobile video was created successfully
    if (!file_exists ($mobile_temp) || filesize ($mobile_temp) < 1024*5) throw new Exception ("The temp Mobile file was not created. The id of the video is: $video->videoId");









    /////////////////////////////////////////////////////////////
    //                        STEP 7                           //
    //            Shift Moov atom on Mobile video              //
    /////////////////////////////////////////////////////////////

    // Debug Log
    $config->debug_conversion ? App::Log (CONVERSION_LOG, "\nShifting moov atom on Mobile video...") : null;

    // Execute Faststart Command
    $faststart_command = "$qt_faststart_path $mobile_temp $mobile >> $debug_log 2>&1";
    Plugin::Trigger ('encode.before_faststart');

    // Debug Log
    $log_msg = "\n\n\n\n==================================================================\n";
    $log_msg .= "FASTSTART\n";
    $log_msg .= "==================================================================\n\n";
    $log_msg .= "Faststart Command: $faststart_command\n";
    $log_msg .= "Faststart Output:\n\n";
    $config->debug_conversion ? App::Log (CONVERSION_LOG, 'Faststart Command: ' . $faststart_command) : null;
    App::Log ($debug_log, $log_msg);

    // Execute Faststart command
    exec ($faststart_command);
    Plugin::Trigger ('encode.faststart');

    // Debug Log
    $config->debug_conversion ? App::Log (CONVERSION_LOG, 'Verifying final Mobile file was created...') : null;

    // Verify Mobile video was created successfully
    if (!file_exists ($mobile) || filesize ($mobile) < 1024*5) throw new Exception ("The final Mobile file was not created. The id of the video is: $video->videoId");









    /////////////////////////////////////////////////////////////
    //                        STEP 5                           //
    //                  Get Video Duration                     //
    /////////////////////////////////////////////////////////////

    // Debug Log
    $config->debug_conversion ? App::Log (CONVERSION_LOG, "\nRetrieving video duration...") : null;

    // Retrieve duration of raw video file.
    $duration_cmd = "$ffmpeg_path -i $raw_video 2>&1 | grep Duration:";
    Plugin::Trigger ('encode.before_get_duration');
    exec ($duration_cmd, $duration_results);

    // Debug Log
    $config->debug_conversion ? App::Log (CONVERSION_LOG, "Duration command results:\n" . print_r ($duration_results, TRUE)) : null;

    $duration_results_cleaned = preg_replace ('/^\s*Duration:\s*/', '', $duration_results[0]);
    preg_match ('/^[0-9]{2}:[0-9]{2}:[0-9]{2}/', $duration_results_cleaned, $duration);
    $sec = Functions::DurationInSeconds ($duration[0]);

    // Debug Log
    $config->debug_conversion ? App::Log (CONVERSION_LOG, "Duration in Seconds: $sec") : null;

    // Calculate thumbnail position
    $thumb_position = round ($sec / 2);
    Plugin::Trigger ('encode.get_duration');

    // Debug Log
    $config->debug_conversion ? App::Log (CONVERSION_LOG, "Thumb Position: $thumb_position") : null;









    /////////////////////////////////////////////////////////////
    //                        STEP 6                           //
    //                Create Thumbnail Image                   //
    /////////////////////////////////////////////////////////////

    // Debug Log
    $config->debug_conversion ? App::Log (CONVERSION_LOG, "\nPreparing to create video thumbnail...") : null;

    // Create video thumbnail image
    $thumb_command = "$ffmpeg_path -i $raw_video -ss $thumb_position " . Settings::Get('thumb_options') . " $thumb >> $debug_log 2>&1";
    Plugin::Trigger ('encode.before_create_thumbnail');

    // Debug Log
    $log_msg = "\n\n\n\n==================================================================\n";
    $log_msg .= "THUMB CREATION\n";
    $log_msg .= "==================================================================\n\n";
    $log_msg .= "Thumb Creation Command: $thumb_command\n\n";
    $log_msg .= "Thumb Creation Output:\n\n";
    $config->debug_conversion ? App::Log (CONVERSION_LOG, "Thumbnail Creation Command: " . $thumb_command) : null;
    App::Log ($debug_log, $log_msg);

    exec ($thumb_command);    // Execute Thumb Creation Command
    Plugin::Trigger ('encode.create_thumbnail');

    // Debug Log
    $config->debug_conversion ? App::Log (CONVERSION_LOG, 'Verifying valid thumbnail was created...') : null;

    // Verify valid thumbnail was created
    if (!file_exists ($thumb) || filesize ($thumb) == 0) throw new Exception ("The video thumbnail is invalid. The id of the video is: $video->videoId");









    /////////////////////////////////////////////////////////////
    //                        STEP 7                           //
    //               Update Video Information                  //
    /////////////////////////////////////////////////////////////

    // Debug Log
    $config->debug_conversion ? App::Log (CONVERSION_LOG, "\nUpdating video information...") : null;

    // Update database with new video status information
    $video->duration = Functions::formatDuration($duration[0]);
    Plugin::Trigger ('encode.before_update');
    $videoMapper->save($video);
    Plugin::Trigger ('encode.update');

    // Activate video
    $videoService = new VideoService();
    $videoService->approve($video, 'activate');









    /////////////////////////////////////////////////////////////
    //                         STEP 8                          //
    //                        Clean up                         //
    /////////////////////////////////////////////////////////////

    try {
        // Debug Log
        $config->debug_conversion ? App::Log (CONVERSION_LOG, 'Deleting raw video...') : null;

        // Delete pre-faststart files
        Filesystem::delete($h264TempFilePath);
        Filesystem::delete($mobile_temp);

        // Delete original video
        if (Settings::Get('keepOriginalVideo') != '1') {
            Filesystem::delete($raw_video);
        }

        // Delete encoding log files
        if ($config->debug_conversion) {
            App::Log (CONVERSION_LOG, "Video ID: $video->videoId, has completed processing!\n");
        } else {
            Filesystem::delete($debug_log);
        }
    } catch (Exception $e) {
        App::Alert ('Error During Video Encoding', $e->getMessage());
        App::Log (CONVERSION_LOG, $e->getMessage());
    }

    Plugin::Trigger ('encode.complete');

} catch (Exception $e) {
    App::Alert ('Error During Video Encoding', $e->getMessage());
    App::Log (CONVERSION_LOG, $e->getMessage());
    exit();
}