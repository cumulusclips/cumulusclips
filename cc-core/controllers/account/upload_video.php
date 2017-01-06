<?php

Plugin::triggerEvent('upload_video.start');

// Verify if user registrations are enabled
$config = Registry::get('config');
if (!$config->enableUserUploads) App::throw404();

// Verify if user is logged in
$userService = new UserService();
$this->view->vars->loggedInUser = $userService->loginCheck();
Functions::redirectIf($this->view->vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$tempFilePrefix = UPLOAD_PATH . '/temp/' . $this->view->vars->loggedInUser->userId . '-video-';
App::enableUploadsCheck();
unset($_SESSION['upload']);

// Handle form if submitted
if (
    isset($_POST['submitted'])
    && !empty($_POST['upload']['temp'])
    && preg_match(
        '/^' . preg_quote($tempFilePrefix, '/') . '[0-9]+/',
        $_POST['upload']['temp']
    )
    && file_exists($_POST['upload']['temp'])
) {
    // Store uploaded file into session
    $_SESSION['upload'] = (object) array(
        'temp' => $_POST['upload']['temp'],
        'time' => time()
    );

    // Move to video information page
    header('Location: ' . HOST . '/account/upload/info/');
    exit();
}

Plugin::triggerEvent('upload_video.end');