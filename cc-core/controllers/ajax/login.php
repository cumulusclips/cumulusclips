<?php

// Verify if user is logged in
$loggedInUser = $this->authService->getAuthUser();
Functions::redirectIf(!$loggedInUser, HOST . '/account/');

// Establish page variables, objects, arrays, etc
$userService = new UserService();
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

    if ($user = $this->authService->validateCredentials($username, $password)) {
        $this->authService->login($user);
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