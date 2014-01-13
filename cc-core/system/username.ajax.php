<?php

// Init view
Plugin::triggerEvent('username.ajax.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf(!$view->vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$userMapper = new UserMapper();

// Check if username is in use
if (!empty($_POST['username']) && strlen($_POST['username']) >= 4) {
    if ($userMapper->getUserByUsername($_POST['username'])) {
        echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText('error_username_unavailable')));
    } else {
        echo json_encode (array ('result' => 1, 'msg' => (string) Language::GetText('username_available')));
    }
} else {
    echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText('username_minimum')));
}