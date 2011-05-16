<?php

### Created on January 6, 2010
### Created by Miguel A. Hurtado
### This script grabs a YouTube video and saves it to the site temp directory

// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('Video');
App::LoadClass ('YouTube');
Plugin::Trigger ('grab.ajax.start');


// Establish page variables, objects, arrays, etc
$url = NULL;



// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, "\n\n### YouTube Grab Validator Called...") : null;
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, "HTTP POST data:\n" . print_r ($_POST, TRUE)) : null;
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Validating passed video...') : null;

### Retrieve video information
if (isset ($_POST['token'])) {

    $token = $db->Escape ($_POST['token']);
    $query = "SELECT video_id FROM videos WHERE MD5(CONCAT(video_id,'" . SECRET_KEY . "')) = '$token' AND status = 2";
    $result = $db->Query ($query);
    if ($db->Count ($result) == 1) {
        $row = $db->FetchObj ($result);
        $video = new Video ($row->video_id);
        $filename = UPLOAD_PATH . '/temp/' . $video->filename . '.flv';
        Plugin::Trigger ('grab.ajax.load_video');
    } else {
        header ('Location: ' . HOST . '/myaccount/upload/');
        exit();
    }

} else {
    header ('Location: ' . HOST . '/myaccount/upload/');
    exit();
}




// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Validating Submitted YouTube URL...') : null;

### Validate submitted URL
if (!empty ($_POST['url']) && !ctype_space ($_POST['url'])) {

    if (preg_match ('/^http:\/\/.*?youtube\.com.*/', $_POST['url'])) {

        // Retrieve video
        $url = trim ($_POST['url']);
        $youtube = new YouTube ($url);
        Plugin::Trigger ('grab.ajax.before_validate_video');
        if (!$youtube->ValidateUrl()) {
            echo 'invalidurl';
            exit();
        }

    } else {
        echo 'invalidurl';
        exit();
    }

} else {
    echo 'invalidurl';
    exit();
}




// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Updating Video Information...') : null;

### Update video info & execute downloading
$data = array ('original_extension' => $youtube->GetBestQualityFormat(), 'status' => 3);
Plugin::Trigger ('grab.ajax.before_update_video');
$video->Update ($data);




// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Calling YouTube Downloader...') : null;

### Initiate Converter
$cmd_output = DEBUG_CONVERSION ? CONVERSION_LOG : '/dev/null';
$converter_cmd = 'nohup ' . $config->php . ' ' . DOC_ROOT . '/cc-core/system/grab.php --video="' . $video->video_id . '" --url="' . urlencode ($url) . '" >> ' .  $cmd_output . ' &';
Plugin::Trigger ('grab.ajax.before_grab');
system ($converter_cmd);
Plugin::Trigger ('grab.ajax.grab');




// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'YouTube Grab Command: ' . $converter_cmd) : null;
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'YouTube Downloader has been called.') : null;

// Notify Upload AJAX of success
echo 'success';

?>