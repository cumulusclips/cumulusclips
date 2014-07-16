<?php

Plugin::triggerEvent('upload.start');

// Verify if user is logged in
$userService = new UserService();
$loggedInUser = $userService->loginCheck();
Functions::RedirectIf($loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
App::EnableUploadsCheck();
$videoMapper = new VideoMapper();
$this->view->disableView = true;

// Retrieve video information
if (!isset ($_POST['timestamp'])) App::Throw404();

// Validate upload key
$upload_key = md5(md5($_POST['timestamp']) . SECRET_KEY);
if (!isset($_SESSION['upload_key']) || $_SESSION['upload_key'] != $upload_key) App::Throw404();

// Validate video
$video = $videoMapper->getVideoByCustom(array('video_id' => $_SESSION['upload'], 'status' => 'new'));
Plugin::triggerEvent('upload.ajax.load_video');
if (!isset($_SESSION['upload']) || !$video) {
    header('Location: ' . HOST . '/myaccount/upload/');
    exit();
}

try {
    // Verify upload was made
    if (empty($_FILES) || !isset($_FILES['upload']['name'])) {
        throw new Exception(Language::getText('error_uploadify_empty')) ;
    }

    // Check for upload errors
    if ($_FILES['upload']['error'] != 0) {
        App::Alert('Error During Video Upload', 'There was an HTTP FILE POST error (Error code #' . $_FILES['upload']['error'] . '). Video ID: ' . $video->videoId);
        throw new Exception(Language::getText('error_uploadify_system', array('host' => HOST)));
    }

    // Validate filesize
    if ($_FILES['upload']['size'] > $config->video_size_limit || filesize($_FILES['upload']['tmp_name']) > $config->video_size_limit) {
        throw new Exception(Language::getText('error_uploadify_filesize'));
    }

    // Validate video extension
    $extension = Functions::GetExtension($_FILES['upload']['name']);
    if (!preg_match("/$extension/i", Functions::GetVideoTypes('fileDesc'))) {
        throw new Exception(Language::getText('error_uploadify_extension'));
    }

    // Move video to site temp directory
    $target = UPLOAD_PATH . '/temp/' . $video->filename . '.' . $extension;
    Plugin::triggerEvent('upload.ajax.before_move_video');
    if (!@move_uploaded_file($_FILES['upload']['tmp_name'], $target)) {
        App::Alert('Error During Video Upload', 'The raw video file transfer failed. Video File: ' . $target);
        throw new Exception(Language::getText('error_uploadify_system', array('host' => HOST)));
    }

    // Change permissions on raw video file
    Plugin::triggerEvent('upload.ajax.before_change_permissions');
    try {
        Filesystem::setPermissions($target, 0644);
    } catch (Exception $e) {
        App::Alert('Error During Video Upload', $e->getMessage());
        throw new Exception(Language::getText('error_uploadify_system', array('host' => HOST)));
    }

    // Update video information
    $video->status = 'pendingConversion';
    $video->originalExtension = $extension;
    Plugin::triggerEvent('upload.ajax.before_update_video');
    $videoMapper->save($video);

    // Initilize Encoder
    $cmd_output = $config->debug_conversion ? CONVERSION_LOG : '/dev/null';
    Plugin::triggerEvent('upload.ajax.before_encode');
    $converter_cmd = 'nohup ' . Settings::Get('php') . ' ' . DOC_ROOT . '/cc-core/system/encode.php --video="' . $video->videoId . '" >> ' .  $cmd_output . ' 2>&1 &';
    exec($converter_cmd);
    Plugin::triggerEvent('upload.ajax.encode');

    // Output success message
    exit(json_encode(array('result' => 1, 'message' => '')));
} catch (Exception $e) {
    exit(json_encode(array('result' => 0, 'message' => $e->getMessage())));
}