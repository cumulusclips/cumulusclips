<?php

Plugin::Trigger ('activate.start');

// Verify if user is logged in
$userService = new UserService();
$this->view->vars->loggedInUser = $userService->loginCheck();

// Establish page variables, objects, arrays, etc
Functions::RedirectIf (!$this->view->vars->logged_in, HOST . '/myaccount/');
$this->view->vars->message = null;

### Verify token was provided
if (isset ($_GET['token'])) {
	
    $token = $_GET['token'];
    $userMapper = new UserMapper();
    $user = $userMapper->getUserByCustom(array('confirm_code' => $token, 'status' => 'new'));
    if ($user) {
        $userService->approve($user, 'activate');
        if (Settings::Get('auto_approve_users') == 1) {
            $this->view->vars->message = Language::GetText ('activate_success', array ('host' => HOST));
            $_SESSION['user_id'] = $user->userId;
        } else {
            $this->view->vars->message = Language::GetText ('activate_approve');
        }
        $this->view->vars->message_type = 'success';
        Plugin::Trigger ('activate.activate');
    } else {
        $this->view->vars->message = Language::GetText ('activate_error', array ('host' => HOST));
        $this->view->vars->message_type = 'error';
    }
	
} else {
    App::Throw404();
}

Plugin::Trigger ('activate.before_render');