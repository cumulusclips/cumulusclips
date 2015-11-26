<?php

Plugin::triggerEvent('activate.start');

// Verify if user is logged in
$userService = new UserService();
$this->view->vars->loggedInUser = $userService->loginCheck();

// Establish page variables, objects, arrays, etc
Functions::redirectIf(!$this->view->vars->loggedInUser, HOST . '/account/');
$this->view->vars->message = null;

// Verify token was provided
if (!empty($_GET['token'])) {
    $token = $_GET['token'];
    $userMapper = new UserMapper();
    $user = $userMapper->getUserByCustom(array('confirm_code' => $token, 'status' => 'new'));
    if ($user) {
        $userService->approve($user, 'activate');
        if (Settings::get('auto_approve_users') == '1') {
            $this->view->vars->message = Language::getText('activate_success', array('host' => HOST));
            $_SESSION['loggedInUserId'] = $user->userId;
        } else {
            $this->view->vars->message = Language::getText('activate_approve');
        }
        $this->view->vars->messageType = 'success';
    } else {
        $this->view->vars->message = Language::getText('activate_error', array('host' => HOST));
        $this->view->vars->messageType = 'errors';
    }
} else {
    App::throw404();
}

Plugin::triggerEvent('activate.end');