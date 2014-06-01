<?php

Plugin::triggerEvent('upload_video.start');

// Verify if user is logged in
$userService = new UserService();
$this->view->vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf($this->view->vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
App::EnableUploadsCheck();
$this->view->vars->timestamp = time();
$videoMapper = new VideoMapper();

// Verify user entered video information
if (isset($_SESSION['upload'])) {
    $video = $videoMapper->getVideoById($_SESSION['upload']);
    if ($video) {
        $_SESSION['upload_key'] = md5(md5($this->view->vars->timestamp) . SECRET_KEY);
    } else {
        header('Location: ' . HOST . '/myaccount/upload/');
        exit();
    }
} else {
    header('Location: ' . HOST . '/myaccount/upload/');
    exit();
}

Plugin::triggerEvent('upload_video.before_render');