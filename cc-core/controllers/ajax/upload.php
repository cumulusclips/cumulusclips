<?php

/**
 * File Upload Validator
 *
 * Uploaded file is validated for filesize, and extension according to the
 * upload type. There are only 4 upload types, these are videos, images,
 * library/attachment files, and addons.
 *
 * If the uploaded file is valid, the uploaded file is moved to the
 * CumulusClips temp directory with a temporary name. The temporary name
 * consists of the current user's user id, upload type, and current Unix
 * timestamp separated by hyphens, i.e. 123-video-1483998751. The client is then
 * provided with the absolute path to the temporary file. Otherwise, if uploaded
 * file is invalid, the client is provided with a reason why.
 */

// Verify if user is logged in
$this->authService->enforceAuth();
$loggedInUser = $this->authService->getAuthUser();

// Establish page variables, objects, arrays, etc
$userService = new UserService();
$config = Registry::get('config');
$validateExtension = true;
$tempFile = UPLOAD_PATH . '/temp/' . $loggedInUser->userId . '-';
$uploadTypes = array('video', 'library', 'image');

// Determine if user is allowed to upload addons
if ($userService->checkPermissions('manage_settings', $loggedInUser)) {
    $uploadTypes[] = 'addon';
}

// Verify post params are valid
if (empty($_POST['upload-type']) || !in_array($_POST['upload-type'], $uploadTypes)) {
    exit('Invalid upload type');
}

try {

    // Create vars based on upload type (video, image, library file, etc.)
    if ($_POST['upload-type'] === 'video') {

        App::enableUploadsCheck();

        // Check if video uploads has been disabled for regular users
        if (
            !$config->enableUserUploads
            && !in_array($loggedInUser->role, array('admin', 'mod'))
        ) {
            App::throw404();
        }

        $maxFilesize = $config->videoSizeLimit;
        $extensionList = $config->acceptedVideoFormats;
        $tempFile .= 'video';
    } elseif ($_POST['upload-type'] === 'image') {
        $maxFilesize = $config->fileSizeLimit;
        $extensionList = $config->acceptedImageFormats;
        $tempFile .= 'image';
    } elseif ($_POST['upload-type'] === 'addon') {
        $maxFilesize = $config->fileSizeLimit;
        $extensionList = array('zip');
        $tempFile .= 'addon';
    } else {
        $maxFilesize = $config->fileSizeLimit;
        $extensionList = array();
        $tempFile .= 'library';
    }

    // Verify upload was made
    if (empty($_FILES) || !isset($_FILES['upload']['name'])) {
        throw new Exception(Language::getText('error_upload_empty'), \ApiResponse::HTTP_BAD_REQUEST);
    }

    // Check for upload errors
    if ($_FILES['upload']['error'] != 0) {
        App::alert('Error During File Upload', 'There was an HTTP FILE POST error (Error code #' . $_FILES['upload']['error'] . ').');
        throw new Exception(
            Language::getText('error_upload_system', array('host' => HOST)),
            \ApiResponse::HTTP_SERVER_ERROR
        );
    }

    // Validate filesize
    if ($_FILES['upload']['size'] > $maxFilesize || filesize($_FILES['upload']['tmp_name']) > $maxFilesize) {
        throw new Exception(Language::getText('error_upload_filesize'), \ApiResponse::HTTP_BAD_REQUEST);
    }

    // Validate image contents
    if ($_POST['upload-type'] === 'image') {
        $handle = fopen($_FILES['upload']['tmp_name'],'r');
        $imageData = fread($handle, filesize($_FILES['upload']['tmp_name']));
        if (!@imagecreatefromstring($imageData)) {
            throw new Exception(Language::getText('error_upload_extension'), \ApiResponse::HTTP_BAD_REQUEST);
        }
    }

    // Validate file extension
    $extension = Functions::getExtension($_FILES['upload']['name']);
    if (!empty($extensionList)) {
        if (!in_array($extension, $extensionList)) {
            throw new Exception(Language::getText('error_upload_extension'), \ApiResponse::HTTP_BAD_REQUEST);
        }
    }

    // Move uploaded file to CumulusClips temp directory
    $tempFile .= '-' . time() . '.' . $extension;
    if (!@move_uploaded_file($_FILES['upload']['tmp_name'], $tempFile)) {
        App::alert('Error During File Upload', 'Uploaded file could not be moved from OS temp directory');
        throw new Exception(
            Language::getText('error_upload_system', array('host' => HOST)),
            \ApiResponse::HTTP_SERVER_ERROR
        );
    }

    // Set read-only permissions for global users
    try {
        Filesystem::setPermissions($tempFile, 0644);
    } catch (Exception $exception) {
        App::alert('Error During File Upload', 'Permissions on uploaded file could not be updated');
        throw new Exception(
            Language::getText('error_upload_system', array('host' => HOST)),
            \ApiResponse::HTTP_SERVER_ERROR
        );
    }

    // Send response
    $apiResponse = new \ApiResponse();
    $apiResponse->statusCode = \ApiResponse::HTTP_CREATED;
    $apiResponse->result = true;
    $apiResponse->other = (object) array('temp' => $tempFile);
    \ApiResponse::sendResponse($apiResponse);

} catch (Exception $e) {

    // Send response
    $apiResponse = new \ApiResponse();
    $apiResponse->statusCode = $e->getCode();
    $apiResponse->result = false;
    $apiResponse->message = $e->getMessage();
    \ApiResponse::sendResponse($apiResponse);
}
