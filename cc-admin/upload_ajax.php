<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$userService = new UserService();
$adminUser = $userService->loginCheck();
Functions::redirectIf($adminUser, HOST . '/login/');
Functions::redirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$validateExtension = true;
$tempFile = UPLOAD_PATH . '/temp/' . $adminUser->userId . '-';
$uploadTypes = array('video', 'library');

// Determine if user is allowed to upload addons
if ($userService->checkPermissions('manage_settings', $adminUser)) {
    $uploadTypes[] = 'addon';
}

// Verify post params are valid
if (empty($_POST['upload-type']) || !in_array($_POST['upload-type'], $uploadTypes)) {
    App::throw404();
}

try {
    // Create vars based on upload type (video, addon,  library file, etc.)
    if ($_POST['upload-type'] == 'video') {
        $maxFilesize = $config->videoSizeLimit;
        $extensionList = $config->acceptedVideoFormats;
        $tempFile .= 'video';
    } else if ($_POST['upload-type'] == 'library') {
        $maxFilesize = $config->fileSizeLimit;
        $validateExtension = false;
        $tempFile .= 'library';
    } else {
        $maxFilesize = 1024*1024*100;
        $extensionList = array('zip');
        $tempFile .= 'addon';
    }
    
    // Verify upload was made
    if (empty($_FILES) || !isset($_FILES['upload']['name'])) {
        throw new Exception('nofile');
    }

    // Check for upload errors
    if ($_FILES['upload']['error'] != 0) {
        App::Alert('Error During File Upload', 'There was an HTTP FILE POST error (Error code #' . $_FILES['upload']['error'] . ').');
        throw new Exception('There was an HTTP FILE POST error');
    }

    // Validate filesize
    if ($_FILES['upload']['size'] > $maxFilesize || filesize($_FILES['upload']['tmp_name']) > $maxFilesize) {
        throw new Exception('File exceeds maximum filesize limit (' . $maxFilesize . ')');
    }

    // Validate file extension
    $extension = Functions::getExtension($_FILES['upload']['name']);
    if ($validateExtension) {
        if (!in_array($extension, $extensionList)) throw new Exception('Upload file type not allowed');
    }

    // Move uploaded file to CumulusClips temp directory
    $tempFile .= '-' . time() . '.' . $extension;
    if (!@move_uploaded_file($_FILES['upload']['tmp_name'], $tempFile)) {
        App::Alert('Error During Admin File Upload', 'Uploaded file could not be moved from OS temp directory');
        throw new Exception('Uploaded file could not be moved from OS temp directory');
    }
} catch (Exception $e) {
    exit(json_encode((object) array(
        'result' => false,
        'message' => $e->getMessage()
    )));
}

// Notify uploader of success
exit(json_encode((object) array(
    'result' => true,
    'message' => 'SUCCESS',
    'other' => (object) array('temp' => $tempFile)
)));
