<?php

### Created on January 7, 2010
### Created by Miguel A. Hurtado
### This script downloads the specified video from YouTube

# $argv[1]: Video ID
# $argv[2]: YouTube Video URL

// Include required files
include (dirname (dirname (dirname ( __FILE__ ))) . '/config/bootstrap.php');
App::LoadClass ('Video');
App::LoadClass ('YouTube');
App::LoadClass ('Encoding');



// Assign variables from shell for use in script
preg_match ('/--video=(.*)$/i', $argv[1], $arg1_matches);
preg_match ('/--url=(.*)$/i', $argv[2], $arg2_matches);
$video_id = $arg1_matches[1];
$youtube_url = urldecode($arg2_matches[1]);




// Debug Log
if (DEBUG_CONVERSION) {
    App::Log (CONVERSION_LOG, "\n\n### YouTube Downloader Called...");
    App::Log (CONVERSION_LOG, "Values passed to downloader:\n" . print_r ($argv, TRUE));
    App::Log (CONVERSION_LOG, 'Validating passed video...');
}

### Validate proccessing video
$video = new Video ($video_id);
if (!$video->found) {
    $msg = "An invalid video was passed to the video downloader.";
    App::Alert ('Error During  YouTube Grab', $msg);
    App::Log (CONVERSION_LOG, "$msg:" . print_r ($argv, TRUE));
    exit();
}




// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Validating passed conversion step...') : null;

### Validate conversion step
if ($video->status != 3) {
    $msg = "An invalid conversion step was passed to the YouTube Downloader.";
    App::Alert ('Error During  YouTube Grab', $msg);
    App::Log (CONVERSION_LOG, "$msg:" . print_r ($argv, TRUE));
    exit();
}




// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Establishing variables...') : null;

### Retrieve video information
$video = new Video ($video_id);
$raw_video = UPLOAD_PATH . '/temp/' . $video->filename;




// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Downloading video...') : null;

### Download video from YouTube
$youtube = new YouTubeGrabber ($youtube_url);
$grab_results = $youtube->DownloadVideo ($raw_video);




// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Verifying download...') : null;

### Verify download was successful
if ($grab_results) {

    // Debug Log
    DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Calling Upload Converter...') : '';
    
    ### Initiate Converter
    if (!LIVE) exit();  // Skip Transcoding
    $cmd_output = DEBUG_CONVERSION ? CONVERSION_LOG : '/dev/null';
    $converter_cmd = 'nohup ' . $config->php . ' ' . DOC_ROOT . '/cc-core/controllers/myaccount/upload_converter.php --video="' . $video->video_id . '" >> ' .  $cmd_output . ' &';
    system ($converter_cmd);




    // Debug Log
    DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Upload Converter Command: ' . $converter_cmd) : null;
    DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Upload Converter has been called.') : null;

} else {
    App::Log (CONVERSION_LOG, 'The YouTube video file download was not successful. The id of the video is: ' . $video->video_id);
    App::Alert ('Error During YouTube Grab', 'The YouTube video file was not created. The id of the video is: ' . $video->video_id);
    exit();
}

?>