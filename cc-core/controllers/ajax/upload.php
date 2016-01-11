<?php

// Verify if user registrations are enabled
$config = Registry::get('config');
if (!$config->enableUserUploads) App::throw404();

// Verify if user is logged in
$userService = new UserService();
$loggedInUser = $userService->loginCheck();
Functions::RedirectIf($loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$videoService = new VideoService();
$config = Registry::get('config');
$filename = $videoService->generateFilename() . '.';

try {
    // Verify upload was made
    if (empty($_FILES) || !isset($_FILES['upload']['name'])) {
        throw new Exception(Language::getText('error_upload_empty')) ;
    }
    
    // Check for upload errors
    if ($_FILES['upload']['error'] != 0) {
        App::Alert('Error During Video Upload', 'There was an HTTP FILE POST error (Error code #' . $_FILES['upload']['error'] . ').');
        throw new Exception(Language::getText('error_upload_system', array('host' => HOST)));
    }
    
    // Validate filesize
    if ($_FILES['upload']['size'] > $config->videoSizeLimit || filesize($_FILES['upload']['tmp_name']) > $config->videoSizeLimit) {
        throw new Exception(Language::getText('error_upload_filesize'));
    }

    // Validate video extension
    $extension = Functions::getExtension($_FILES['upload']['name']);
    if (!preg_match('/^' . implode('|', $config->acceptedVideoFormats) . '$/i', $extension)) {
        throw new Exception(Language::getText('error_upload_extension'));
    }

    // Move video to temp dir
    $filename .= $extension;
    $target = UPLOAD_PATH . '/temp/' . $filename;
    if (!@move_uploaded_file($_FILES['upload']['tmp_name'], $target)) {
        App::Alert('Error During Video Upload', 'The raw uploaded video file could not be moved from OS temp directory. Video File: ' . $target);
        throw new Exception(Language::getText('error_upload_system', array('host' => HOST)));
    }
} catch (Exception $e) {
    exit(json_encode((object) array(
        'result' => false,
        'message' => $e->getMessage()
    )));
}

// Notify client of success
exit(json_encode((object) array(
    'result' => true,
    'message' => 'SUCCESS',
    'other' => (object) array('filename' => $filename)
)));