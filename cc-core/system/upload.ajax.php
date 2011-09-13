<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');
App::LoadClass ('Filesystem');
Plugin::Trigger ('upload.ajax.start');
App::EnableUploadsCheck();


### Retrieve video information
if (!isset ($_POST['token'], $_POST['timestamp'])) App::Throw404();

session_write_close();
session_id ($_POST['token']);
session_start();


// Validate upload key
$upload_key = md5 (md5 ($_POST['timestamp']) . SECRET_KEY);
if (!isset ($_SESSION['upload_key']) || $_SESSION['upload_key'] != $upload_key) App::Throw404();
Functions::RedirectIf ($logged_in = User::LoginCheck(), HOST . '/login/');
$user = new User ($logged_in);


if (isset ($_SESSION['upload']) && Video::Exist (array('video_id' => $_SESSION['upload'], 'status' => 'new'))) {
    $video = new Video ($_SESSION['upload']);
    Plugin::Trigger ('upload.ajax.load_video');
} else {
    header ('Location: ' . HOST . '/myaccount/upload/');
    exit();
}




try {

    ### Verify upload was made
    if (empty ($_FILES) || !isset ($_FILES['upload']['name'])) {
        throw new Exception (Language::GetText('error_uploadify_empty')) ;
    }



    ### Check for upload errors
    if ($_FILES['upload']['error'] != 0) {
        App::Alert ('Error During Video Upload', 'There was an HTTP FILE POST error (Error code #' . $_FILES['upload']['error'] . '). Video ID: ' . $video->video_id);
        throw new Exception (Language::GetText('error_uploadify_system', array ('host' => HOST)));
    }



    ### Validate filesize
    if ($_FILES['upload']['size'] > $config->video_size_limit || filesize ($_FILES['upload']['tmp_name']) > $config->video_size_limit) {
        throw new Exception (Language::GetText('error_uploadify_filesize'));
    }



    ### Validate video extension
    $extension = Functions::GetExtension ($_FILES['upload']['name']);
    if (!in_array ($extension, Functions::GetVideoTypes())) {
        throw new Exception (Language::GetText('error_uploadify_extension'));
    }



    ### Move video to site temp directory
    $target = UPLOAD_PATH . '/temp/' . $video->filename . '.' . $extension;
    Plugin::Trigger ('upload.ajax.before_move_video');
    if (!@move_uploaded_file ($_FILES['upload']['tmp_name'], $target)) {
        App::Alert ('Error During Video Upload', 'The raw video file transfer failed. Video File: ' . $target);
        throw new Exception (Language::GetText('error_uploadify_system', array ('host' => HOST)));
    }



    ### Change permissions on raw video file
    Plugin::Trigger ('upload.ajax.before_change_permissions');
    try {
        Filesystem::Open();
        Filesystem::SetPermissions ($target, 0644);
        Filesystem::Close();
    } catch (Exception $e) {
        App::Alert ('Error During Video Upload', $e->getMessage());
        throw new Exception (Language::GetText('error_uploadify_system', array ('host' => HOST)));
    }



    ### Update video information
    $data = array ('status' => 'pending conversion', 'original_extension' => $extension);
    Plugin::Trigger ('upload.ajax.before_update_video');
    $video->Update ($data);



    ### Initilize Encoder
    $cmd_output = $config->debug_conversion ? CONVERSION_LOG : '/dev/null';
    Plugin::Trigger ('upload.ajax.before_encode');
    $converter_cmd = 'nohup ' . Settings::Get ('php') . ' ' . DOC_ROOT . '/cc-core/system/encode.php --video="' . $video->video_id . '" >> ' .  $cmd_output . ' 2>&1 &';
    exec ($converter_cmd);
    Plugin::Trigger ('upload.ajax.encode');

    // Output success message
    exit (json_encode (array ('result' => 1, 'msg' => '')));

} catch (Exception $e) {
    exit (json_encode (array ('result' => 0, 'msg' => $e->getMessage())));
}

?>