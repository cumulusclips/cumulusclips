<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');
App::LoadClass ('Filesystem');


// Retrieve video information
if (!isset ($_POST['token'], $_POST['timestamp'])) App::Throw404();


// Load main session and validate login
session_write_close();
session_id ($_POST['token']);
session_start();
Functions::RedirectIf ($logged_in = User::LoginCheck(), HOST . '/login/');
$admin = new User ($logged_in);
Functions::RedirectIf (User::CheckPermissions ('admin_panel', $admin), HOST . '/myaccount/');


// Validate video upload key
$video_upload_key = md5 (md5 ($_POST['timestamp']) . SECRET_KEY);
if (!isset ($_SESSION['video_upload_key']) || $_SESSION['video_upload_key'] != $video_upload_key) App::Throw404();




try {
    
    ### Verify upload was made
    if (empty ($_FILES) || !isset ($_FILES['upload']['name'])) throw new Exception ('nofile') ;
    


    ### Check for upload errors
    if ($_FILES['upload']['error'] != 0) {
        App::Alert ('Error During Processing', 'There was an HTTP FILE POST error (Error code #' . $_FILES['upload']['error'] . ').');
        throw new Exception ('error');
    }



    ### Validate filesize
    if ($_FILES['upload']['size'] > $config->video_size_limit || filesize ($_FILES['upload']['tmp_name']) > $config->video_size_limit) throw new Exception ('filesize');



    ### Validate video extension
    $extension = Functions::GetExtension ($_FILES['upload']['name']);
    if (!in_array ($extension, $config->accepted_video_formats)) throw new Exception ('extension');



    ### Move video to site temp directory
    $target = UPLOAD_PATH . '/temp/' . Video::CreateFilename() . '.' . $extension;
    if (!@move_uploaded_file ($_FILES['upload']['tmp_name'], $target)) {
        App::Alert ('Error During Processing', 'The raw video file transfer failed.');
        throw new Exception ('error');
    }



    ### Change permissions on raw video file
    try {
        Filesystem::Open();
        Filesystem::SetPermissions ($target, 0644);
    } catch (Exception $e) {
        App::Alert ('Error During Processing', 'Could not change the permissions on the raw video file.');
        throw new Exception($e->getMessage());
    }

} catch (Exception $e) {
    exit (json_encode (array ('status' => $e->getMessage(), 'message' => '')));
}

### Notify Upload AJAX of success
$_SESSION['video'] = serialize (array ('key' => $video_upload_key, 'name' => $_FILES['upload']['name'], 'temp' => $target));
exit (json_encode (array ('status' => 'success', 'message' => $_FILES['upload']['name'])));

?>