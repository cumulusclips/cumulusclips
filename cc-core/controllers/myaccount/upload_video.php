<?php

// Init view
View::initView('upload_video');
Plugin::triggerEvent('upload_video.start');

// Verify if user is logged in
$userService = new UserService();
View::$vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf(View::$vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
App::EnableUploadsCheck();
View::$vars->timestamp = time();
$videoMapper = new VideoMapper();

// Verify user entered video information
if (isset($_SESSION['upload'])) {
    $video = $videoMapper->getVideoById($_SESSION['upload']);
    if ($video) {
        $_SESSION['upload_key'] = md5(md5(View::$vars->timestamp) . SECRET_KEY);
    } else {
        header('Location: ' . HOST . '/myaccount/upload/');
        exit();
    }
} else {
    header('Location: ' . HOST . '/myaccount/upload/');
    exit();
}

// Output page
Plugin::triggerEvent('upload_video.before_render');
View::render('myaccount/upload_video.tpl');