<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/config/admin.bootstrap.php');

// Verify if user is logged in
$userService = new UserService();
$adminUser = $userService->loginCheck();
Functions::redirectIf($adminUser, HOST . '/login/');
Functions::redirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$videoService = new VideoService();

// Verify post params are valid
if (empty($_POST['upload-type']) || !in_array($_POST['upload-type'], array('addon', 'video'))) {
    App::throw404();
}

try {
    // Create vars based on upload type (video | addon)
    if ($_POST['upload-type'] == 'video') {
        $maxFilesize = $config->video_size_limit;
        $extensionList = $config->accepted_video_formats;
        $temp = UPLOAD_PATH . '/temp';
        $fileName = $videoService->generateFilename() . '.';
        $createDir = false;
    } else {
        $maxFilesize = 1024*1024*100;
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
    if ($_FILES['upload']['size'] > $maxFilesize || filesize($_FILES['upload']['tmp_name']) > $maxFilesize) {
        throw new Exception('filesize');
    }

    // Validate file extension
    $extension = Functions::getExtension($_FILES['upload']['name']);
    if (!in_array($extension, $extensionList)) throw new Exception('extension');

    // Create temp dir
    if ($createDir) {
        Filesystem::createDir($temp);
        Filesystem::setPermissions($temp, 0777);
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

// Notify Uploadify of success
exit(json_encode((object) array(
    'result' => true,
    'message' => 'SUCCESS',
    'other' => (object) array('temp' => "$temp/$fileName")
)));