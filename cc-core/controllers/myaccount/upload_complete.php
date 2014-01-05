<?php

// Init view
View::initView('upload_complete');
Plugin::triggerEvent('upload_complete.start');

// Verify if user is logged in
$userService = new UserService();
View::$vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf(View::$vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
App::EnableUploadsCheck();

// Verify user completed upload process
if (isset($_SESSION['upload'])) {
    unset($_SESSION['upload']);
} else {
    header('Location: ' . HOST . '/myaccount/upload/video/');
    exit();
}

// Output page
Plugin::triggerEvent('upload_complete.before_render');
View::render('myaccount/upload_complete.tpl');