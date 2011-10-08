<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Plugin');


// Retrieve video information
if (!isset ($_POST['token'], $_POST['timestamp'])) App::Throw404();


// Load main session and validate login
session_write_close();
session_id ($_POST['token']);
session_start();
Functions::RedirectIf ($logged_in = User::LoginCheck(), HOST . '/login/');
$admin = new User ($logged_in);
Functions::RedirectIf (User::CheckPermissions ('admin_panel', $admin), HOST . '/myaccount/');


// Validate file upload key
$upload_key = md5 (md5 ($_POST['timestamp']) . SECRET_KEY);
if (!isset ($_SESSION['upload_key']) || $_SESSION['upload_key'] != $upload_key) App::Throw404();



try {
    
    ### Verify upload was made
    if (empty ($_FILES) || !isset ($_FILES['upload']['name'])) throw new Exception ('nofile') ;
    

    ### Check for upload errors
    if ($_FILES['upload']['error'] != 0) {
        App::Alert ('Error During Plugin Upload', 'There was an HTTP FILE POST error (Error code #' . $_FILES['upload']['error'] . ').');
        throw new Exception ('error');
    }


    ### Validate filesize
    if ($_FILES['upload']['size'] > 1024*1024*100 || filesize ($_FILES['upload']['tmp_name']) > 1024*1024*100) throw new Exception ('filesize');


    ### Validate video extension
    $extension = Functions::GetExtension ($_FILES['upload']['name']);
    if ($extension != 'zip') throw new Exception ('extension');


    ### Move video to site temp directory
    
    // Create temp dir
    $temp = DOC_ROOT . '/cc-content/.add-plugin';
    Filesystem::Open();
    Filesystem::CreateDir ($temp);
    Filesystem::SetPermissions ($temp, 0777);

    // Move zip to temp dir
    if (!@move_uploaded_file ($_FILES['upload']['tmp_name'], $temp . '/plugin.zip')) {
        App::Alert ('Uploaded file could not be moved from OS temp directory');
        throw new Exception ('error');
    }

} catch (Exception $e) {
    exit (json_encode (array ('status' => $e->getMessage(), 'message' => '')));
}

### Notify Uploadify of success
$_SESSION['upload'] = serialize (array ('key' => $upload_key, 'name' => $_FILES['upload']['name'], 'temp' => $temp . '/plugin.zip'));
exit (json_encode (array ('status' => 'success', 'message' => $_FILES['upload']['name'])));

?>