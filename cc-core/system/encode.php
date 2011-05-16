<?php

### Created on May 11, 2009
### Created by Miguel A. Hurtado
### This script processes an uploaded video for usage on the site

# $argv[1]: Video ID


// Include required files
include (dirname (dirname (dirname ( __FILE__ ))) . '/config/bootstrap.php');
App::LoadClass ('Video');
App::LoadClass ('User');
App::LoadClass ('Privacy');
App::LoadClass ('EmailTemplate');
Plugin::Trigger ('encode.start');


// Establish page variables, objects, arrays, etc
preg_match ('/--video=(.*)$/i', $argv[1], $arg_matches);
$video_id = $arg_matches[1];
Plugin::Trigger ('encode.parse');




// Debug Log
if (DEBUG_CONVERSION) {
    App::Log (CONVERSION_LOG, "\n\n### Converter Called...");
    App::Log (CONVERSION_LOG, "Values passed to converter:\n" . print_r ($argv, TRUE));
}









/////////////////////////////////////////////////////////////
//                        STEP 1                           //
//               Validate Requested Video                  //
/////////////////////////////////////////////////////////////


// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Validating requested video...') : null;

### Validate requested video
$video = new Video ($video_id);
if (!$video->found) {
    $msg = "An invalid video was passed to the video converter.";
    App::Alert ('Error During Video Encoding', $msg);
    App::Log (CONVERSION_LOG, "$msg:" . print_r ($argv, TRUE));
    exit();
}




// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Establishing variables...') : null;

### Retrieve video information
$video->Update (array ('status' => 5));
$debug_log = LOG . '/' . $video->filename . '.log';
$raw_video = UPLOAD_PATH . '/temp/' . $video->filename . '.' . $video->original_extension;
$flv = UPLOAD_PATH . '/flv/' . $video->filename . '.flv';
$mp4 = UPLOAD_PATH . '/mp4/' . $video->filename . '.mp4';
$thumb = UPLOAD_PATH . '/thumbs/' . $video->filename . '.jpg';
Plugin::Trigger ('encode.load_video');




// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Verifying raw video exists...') : null;

### Verify Raw Video Exists
if (!file_exists ($raw_video)) {
    $msg = "The raw video file does not exists. The id of the video is: $video->video_id";
    App::Alert ('Error During Video Encoding', $msg);
    App::Log (CONVERSION_LOG, $msg);
    exit();
}




// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Verifying raw video was valid size...') : null;

### Verify Raw Video has valid file size
// (Greater than min. 10KB, anything smaller is probably corrupted
if (!filesize ($raw_video) >= 10000) {
    $msg = "The raw video file is not a valid filesize. The id of the video is: $video->video_id";
    App::Alert ('Error During Video Encoding', $msg);
    App::Log (CONVERSION_LOG, $msg);
    exit();
}









/////////////////////////////////////////////////////////////
//                        STEP 2                           //
//               Encode raw video to FLV                   //
/////////////////////////////////////////////////////////////


// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, "\nPreparing for: FLV Encoding...") : null;

### Encode raw video to FLV and MP4 for mobile site
$flv_command = "$config->ffmpeg -i $raw_video -s 640x480 -ab 128k -b 1600k -f flv $flv >> $debug_log 2>&1";
Plugin::Trigger ('encode.before_flv_encode');

// Debug Log
$log_msg = "==================================================================\n";
$log_msg .= "FLV ENCODING\n";
$log_msg .= "==================================================================\n\n";
$log_msg .= "FLV Encoding Command: $flv_command\n\n";
$log_msg .= "FLV Encoding Output:\n\n";
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'FLV Encoding Command: ' . $flv_command) : null;
App::Log ($debug_log, $log_msg);

system ($flv_command);  // Execute FLV Encoding Command
Plugin::Trigger ('encode.flv_encode');




// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Verifying FLV was created successfully...') : null;

### Verify FLV Video was created successfully
if (!file_exists ($flv)) {
    $msg = "The FLV file was not created. The id of the video is: $video->video_id";
    App::Alert ('Error During Video Encoding', $msg);
    App::Log (CONVERSION_LOG, $msg);
    exit();
}








/////////////////////////////////////////////////////////////
//                        STEP 3                           //
//            Encode raw video to MP4 (Mobile)             //
/////////////////////////////////////////////////////////////


// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, "\nPreparing for MP4 (Mobile) Encoding...") : null;

### Encode raw video to FLV and MP4 for mobile site
$mp4_command = "$config->ffmpeg -i $raw_video -s 480x320 -f mp4 $mp4 >> $debug_log 2>&1";
Plugin::Trigger ('encode.before_mp4_encode');

// Debug Log
$log_msg = "\n\n\n\n==================================================================\n";
$log_msg .= "MP4 ENCODING\n";
$log_msg .= "==================================================================\n\n";
$log_msg .= "MP4 Encoding Command: $mp4_command\n\n";
$log_msg .= "MP4 Encoding Output:\n\n";
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'MP4 Encoding Command: ' . $mp4_command) : null;
App::Log ($debug_log, $log_msg);

system ($mp4_command);  // Execute MP4 Encoding Command
Plugin::Trigger ('encode.mp4_encode');




// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Verifying MP4 was created successfully...') : null;

### Verify MP4 Video was created successfully
if (!file_exists ($mp4)) {
    $msg = "The MP4 file was not created. The id of the video is: $video->video_id";
    App::Alert ('Error During Video Encoding', $msg);
    App::Log (CONVERSION_LOG, $msg);
    exit();
}









/////////////////////////////////////////////////////////////
//                        STEP 4                           //
//                  Get Video Duration                     //
/////////////////////////////////////////////////////////////


// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, "\nRetrieving video duration...") : null;

### Retrieve duration of new flv file.
$duration_cmd = "$config->ffmpeg -i $flv 2>&1 | grep Duration:";
Plugin::Trigger ('encode.before_get_duration');
exec ($duration_cmd, $duration_results);

// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, "Duration command results:\n" . print_r ($duration_results, TRUE)) : null;




$duration_results_cleaned = preg_replace ('/^\s*Duration:\s*/', '', $duration_results[0]);
preg_match ('/^[0-9]{2}:[0-9]{2}:[0-9]{2}/', $duration_results_cleaned, $duration);
$sec = Functions::DurationInSeconds ($duration[0]);

// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, "Duration in Seconds: $sec") : null;




// Calculate thumbnail position
$sec2 = $sec / 2;
$sec2 = round ($sec2);
$thumb_position = Functions::FormatSeconds ($sec2);
Plugin::Trigger ('encode.get_duration');

// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, "Thumb Position: $thumb_position") : null;









/////////////////////////////////////////////////////////////
//                        STEP 5                           //
//                Create Thumbnail Image                   //
/////////////////////////////////////////////////////////////


// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, "\nPreparing to create video thumbnail...") : null;

### Create video thumbnail image
$thumb_command = "$config->ffmpeg -i $flv -ss $thumb_position -t 00:00:01 -s 120x90 -r 1 -f mjpeg $thumb >> $debug_log 2>&1";
Plugin::Trigger ('encode.before_create_thumbnail');

// Debug Log
$log_msg = "\n\n\n\n==================================================================\n";
$log_msg .= "THUMB CREATION\n";
$log_msg .= "==================================================================\n\n";
$log_msg .= "Thumb Creation Command: $thumb_command\n\n";
$log_msg .= "Thumb Creation Output:\n\n";
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, "Thumbnail Creation Command: " . $thumb_command) : null;
App::Log ($debug_log, $log_msg);

system ($thumb_command);    // Execute Thumb Creation Command
Plugin::Trigger ('encode.create_thumbnail');




// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Verifying valid thumbnail was created successfully...') : null;

// Verify valid thumbnail was created
if (!file_exists ($thumb) || filesize ($thumb) == 0) {
    $msg = "The video thumbnail is invalid. The id of the video is: $video->video_id";
    App::Alert ('Error During Video Encoding', $msg);
    App::Log (CONVERSION_LOG, $msg);
    exit();
}









/////////////////////////////////////////////////////////////
//                        STEP 6                           //
//               Update database details                   //
/////////////////////////////////////////////////////////////


// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, "\nUpdating video status...") : null;

### Update database with new video status information
$data['duration'] = $duration[0];
$data['status'] = 6;
Plugin::Trigger ('encode.before_update');
$video->Update ($data);
Plugin::Trigger ('encode.update');









/////////////////////////////////////////////////////////////
//                        STEP 7                           //
//               Notify users of new video                 //
/////////////////////////////////////////////////////////////


// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Notifying users of new video...') : null;

### Send subscribers notification if opted-in
$query = "SELECT user_id FROM " . DB_PREFIX . "subscriptions WHERE member = $video->user_id";
$result_alert = $db->Query ($query);
while ($opt = $db->FetchRow ($result_alert)) {

    $subscriber = new User ($opt[0]);
    $privacy = Privacy::LoadByUser ($opt[0]);
    if ($privacy->OptCheck ('new_video')) {
        $template = new EmailTemplate ('/new_video.htm');
        $template_data = array (
            'host'      => HOST,
            'email'  => $subscriber->email,
            'channel'   => $user->username,
            'title'     => $video->title,
            'video_id'  => $video->video_id,
            'dashed'    => $video->dashed
        );
        $template->Replace($template_data);
        $template->Send ($subscriber->email);
    }

}
Plugin::Trigger ('encode.notify_subscribers');









/////////////////////////////////////////////////////////////
//                        STEP 8                           //
//                       Clean up                          //
/////////////////////////////////////////////////////////////


// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Deleting raw video...') : null;

### Delete Original Video
if (!unlink ($raw_video)) {
    $msg = "Unable to delete the raw video. The id of the video is: $video->video_id";
    App::Alert ('Error During Video Encoding', $msg);
    App::Log (CONVERSION_LOG, $msg);
}




### Delete encoding log files
if (DEBUG_CONVERSION) {
    App::Log (CONVERSION_LOG, "Video ID: $video->video_id, has completed processing!\n");
} else {
    unlink ($debug_log);
    if (file_exists ($debug_log)) {
        $msg = "Unable to delete the encoding log. The id of the video is: $video->video_id";
        App::Alert ('Error During Video Encoding', $msg);
    }
}
Plugin::Trigger ('encode.complete');

?>