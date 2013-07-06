<?php

// Include required files
include_once(dirname(dirname(__FILE__)) . '/cc-core/config/admin.bootstrap.php');
App::LoadClass('User');
App::LoadClass('Video');

// Load main session and validate theme
Functions::RedirectIf($logged_in = User::LoginCheck(), HOST . '/login/');
$admin = new User($logged_in);
Functions::RedirectIf(User::CheckPermissions('admin_panel', $admin), HOST . '/myaccount/');

// Verify post params are valid
if (empty($_POST['uploadType']) || !in_array($_POST['uploadType'], array('addon', 'video'))) {
    App::Throw404();
}

try {
    
    // Create vars based on upload type (video | addon)
    if ($_POST['uploadType'] == 'video') {
        $filesize = $config->video_size_limit;
        $extensionList = $config->accepted_video_formats;
        $temp = UPLOAD_PATH . '/temp';
        $fileName = Video::CreateFilename() . '.';
        $createDir = false;
    } else {
        $filesize = 1024*1024*100;
        $extensionList = array('zip');
        $temp = UPLOAD_PATH . '/temp/.' . session_id();
        $fileName = 'addon' . '.';
        $createDir = true;
    }
    
    // Verify upload was made
    if (empty($_FILES) || !isset($_FILES['upload']['name'])) {
        throw new Exception('nofile');
    }

    // Check for upload errors
    if ($_FILES['upload']['error'] != 0) {
        App::Alert('Error During File Upload', 'There was an HTTP FILE POST error (Error code #' . $_FILES['upload']['error'] . ').');
        throw new Exception('error');
    }

    // Validate filesize
    if ($_FILES['upload']['size'] > $filesize || filesize($_FILES['upload']['tmp_name']) > $filesize) {
        throw new Exception('filesize');
    }

    // Validate file extension
    $extension = Functions::GetExtension($_FILES['upload']['name']);
    if (!in_array($extension, $extensionList)) throw new Exception('extension');

    // Create temp dir
    if ($createDir) {
        Filesystem::Open();
        Filesystem::CreateDir($temp);
        Filesystem::SetPermissions($temp, 0777);
    }

    // Move zip to temp dir
    $fileName .= $extension;
    if (!@move_uploaded_file($_FILES['upload']['tmp_name'], "$temp/$fileName")) {
        App::Alert('Error During Admin File Upload', 'Uploaded file could not be moved from OS temp directory');
        throw new Exception('error');
    }

} catch (Exception $e) {
    exit(json_encode((object) array(
        'result' => false,
        'message' => $e->getMessage()
    )));
}

### Notify Uploadify of success
exit(json_encode((object) array(
    'result' => true,
    'message' => 'SUCCESS',
    'other' => (object) array('temp' => "$temp/$fileName")
)));