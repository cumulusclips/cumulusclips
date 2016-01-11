<?php

Plugin::triggerEvent('upload_video.start');

// Verify if user registrations are enabled
$config = Registry::get('config');
if (!$config->enableUserUploads) App::throw404();

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
    $video = $videoMapper->getVideoByCustom(array('video_id' => $_SESSION['upload']));//, 'status' => 'new'));
    if ($video) {
        $this->view->video = $video;
    } else {
        header('Location: ' . HOST . '/account/upload/');
        exit();
    }
} else {
    header('Location: ' . HOST . '/account/upload/');
    exit();
}

Plugin::triggerEvent('upload_video.end');