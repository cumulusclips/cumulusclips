<?php

### Created on February 28, 2009
### Created by Miguel A. Hurtado
### This script displays the site homepage


// Include required files
include ('../cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');
App::LoadClass ('Filesystem');




### Retrieve video information
$config->debug_conversion ? App::Log (CONVERSION_LOG, "\n\n### Admin Upload Validator Called...") : '';
if (!isset ($_POST['token'], $_POST['timestamp'])) App::Throw404();

session_write_close();
session_id ($_POST['token']);
session_start();

// Validate video upload key
$video_upload_key = md5 (md5 ($_POST['timestamp']) . SECRET_KEY);
if (!isset ($_SESSION['video_upload_key']) || $_SESSION['video_upload_key'] != $video_upload_key) App::Throw404();




try {
    
    ### Verify upload was made
    $config->debug_conversion ? App::Log (CONVERSION_LOG, "Uploaded file's data:\n" . print_r ($_FILES, TRUE)) : null;
    if (empty ($_FILES) || !isset ($_FILES['upload']['name'])) throw new Exception ('nofile') ;
    



    ### Check for upload errors
    $config->debug_conversion ? App::Log (CONVERSION_LOG, 'Checking for HTTP FILE POST errors...') : null;
    if ($_FILES['upload']['error'] != 0) {
        App::Alert ('Error During Processing', 'There was an HTTP FILE POST error (Error code #' . $_FILES['upload']['error'] . ').');
        throw new Exception ('error');
    }




    ### Validate filesize
    $config->debug_conversion ? App::Log (CONVERSION_LOG, 'Validating video size...') : null;
    if ($_FILES['upload']['size'] > $config->video_size_limit || filesize ($_FILES['upload']['tmp_name']) > $config->video_size_limit) throw new Exception ('filesize');




    ### Validate video extension
    $config->debug_conversion ? App::Log (CONVERSION_LOG, 'Validating video extension...') : null;
    $extension = Functions::GetExtension ($_FILES['upload']['name']);
    if (!in_array ($extension, $config->accepted_video_formats)) throw new Exception ('extension');
    $data = array ('original_extension' => $extension);




    ### Move video to site temp directory
    $config->debug_conversion ? App::Log (CONVERSION_LOG, 'Moving video to temp directory...') : null;
    $target = UPLOAD_PATH . '/temp/' . Video::CreateFilename() . '.' . $extension;
    if (!@move_uploaded_file ($_FILES['upload']['tmp_name'], $target)) {
        App::Alert ('Error During Processing', 'The raw video file transfer failed.');
        throw new Exception ('error');
    }




    ### Change permissions on raw video file
    $config->debug_conversion ? App::Log (CONVERSION_LOG, 'Updating raw video file permissions...') : null;
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