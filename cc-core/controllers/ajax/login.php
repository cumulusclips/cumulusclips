<?php

// Verify if user is logged in
$userService = new UserService();
$loggedInUser = $userService->loginCheck();
Functions::redirectIf(!$loggedInUser, HOST . '/account/');
$this->view->options->disableView = true;

$username = null;
$password = null;

// Handle login form if submitted
$this->view->vars->login_submit = true;

// validate username
if (!empty($_POST['username'])) {
    $username = trim($_POST['username']);
}

// validate password
if (!empty($_POST['password'])) {
    $password = trim($_POST['password']);
}

// login if no errors were found
if ($username && $password) {

    if ($userService->login($username, $password)) {
        exit(json_encode(array(
            'result' => true,
            'message' => null,
            'other' => null
        )));
    } else {
        exit(json_encode(array(
            'result' => false,
            'message' => Language::getText('error_invalid_login'),
            'other' => null
        )));
    }

} else {
    exit(json_encode(array(
        'result' => false,
        'message' => Language::getText('error_general'),
        'other' => null
    )));
}