<?php

### Created on May 16, 2009
### Created by Miguel A. Hurtado
### This script validates the uploaded video and moves it to the site temp directory

// Include required files
include ('../../config/bootstrap.php');
App::LoadClass ('Video');


// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, "\n\n### Upload Validator Called...") : '';

### Retrieve video information
if (isset ($_POST['token'])) {

    // Debug Log
    DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, "Retrieving video information...") : null;

    $token = $db->Escape ($_POST['token']);
    $query = "SELECT video_id FROM videos WHERE MD5(CONCAT(video_id,'" . SECRET_KEY . "')) = '$token' AND status = 2";
    $result = $db->Query ($query);
    if ($db->Count ($result) == 1) {
        $row = $db->FetchObj ($result);
        $video = new Video ($row->video_id);
    } else {
        header ('Location: ' . HOST . '/myaccount/upload/');
        exit();
    }

} else {
    header ('Location: ' . HOST . '/myaccount/upload/');
    exit();
}




// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, "Uploaded file's data:\n" . print_r ($_FILES, TRUE)) : null;

### Verify upload was made
if (empty ($_FILES) || !isset ($_FILES['uploadify']['name'])) {
    echo 'nofile';
    exit();
}




// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Checking for HTTP FILE POST errors...') : null;

### Check for upload errors
if ($_FILES['uploadify']['error'] != 0) {
    App::Alert ('Error During Processing', 'There was an HTTP FILE POST error (Error code #' . $_FILES['uploadify']['error'] . '). The id of the video is: ' . $video->video_id);
    echo 'error';
    exit();
}




// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Validating video size...') : null;

### Validate filesize
if ($_FILES['uploadify']['size'] > VIDEO_SIZE_LIMIT) {
    echo 'filesize';
    exit();
}




// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Validating video extension...') : null;

### Validate video extension
$extension = Functions::GetExtension ($_FILES['uploadify']['name']);
if (in_array ($extension, $config->accepted_video_extensions)) {
    $data = array ('original_extension' => $extension);
    $video->Update ($data);
} else {
    App::Alert ('Error During Processing', 'Invalid video extension. The id of the video is: ' . $video->video_id);
    echo 'extension';
    exit();
}




// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Moving video to temp directory...') : null;

### Move video to site temp directory
$target = UPLOAD_PATH . '/temp/' . $video->filename . '.' . $extension;
if (!@move_uploaded_file ($_FILES['uploadify']['tmp_name'], $target)) {
    App::Alert ('Error During Processing', 'The raw video file transfer failed. The id of the video is: ' . $video->video_id);
    echo 'error';
    exit();
}




// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Verifying raw video was moved to temp directory...') : null;

### Make sure file was moved
if (!file_exists ($target)) {
    App::Alert ('Error During Processing', 'The raw video file was not moved. The id of the video is: ' . $video->video_id);
    echo 'error';
    exit();
}




// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Updating raw video file permissions...') : null;

### Change permissions on raw video file
if (!chmod ($target, 0755)) {
    App::Alert ('Error During Processing', 'Could not change the permissions on the raw video file. The id of the video is: ' . $video->video_id);
    echo 'error';
    exit();
}


### Update upload stutus & execute processing
$video->Update (array ('status' => 4));




// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Calling Upload Converter...') : '';

### Initiate Converter
if (!LIVE) exit('success'); // Skip Conversion
$cmd_output = DEBUG_CONVERSION ? CONVERSION_LOG : '/dev/null';
$converter_cmd = 'nohup ' . $config->php . ' ' . DOC_ROOT . '/cc-core/controllers/myaccount/upload_converter.php --video="' . $video->video_id . '" >> ' .  $cmd_output . ' &';
system ($converter_cmd);




// Debug Log
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Upload Converter Command: ' . $converter_cmd) : null;
DEBUG_CONVERSION ? App::Log (CONVERSION_LOG, 'Upload Converter has been called.') : null;

### Notify Upload AJAX of success
echo 'success';

?>