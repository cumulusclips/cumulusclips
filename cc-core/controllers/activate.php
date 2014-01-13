<?php

// Establish page variables, objects, arrays, etc
$view->InitView ('activate');
Plugin::Trigger ('activate.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();

Functions::RedirectIf (!$view->vars->logged_in, HOST . '/myaccount/');
$view->vars->message = null;

### Verify token was provided
if (isset ($_GET['token'])) {
	
    $token = $_GET['token'];
    $userMapper = new UserMapper();
    $user = $userMapper->getUserByCustom(array('confirm_code' => $token, 'status' => 'new'));
    if ($user) {
        $userService->approve($user, 'activate');
        if (Settings::Get('auto_approve_users') == 1) {
            $view->vars->message = Language::GetText ('activate_success', array ('host' => HOST));
            $_SESSION['user_id'] = $user->userId;
        } else {
            $view->vars->message = Language::GetText ('activate_approve');
        }
        $view->vars->message_type = 'success';
        Plugin::Trigger ('activate.activate');
    } else {
        $view->vars->message = Language::GetText ('activate_error', array ('host' => HOST));
        $view->vars->message_type = 'error';
    }
	
} else {
    App::Throw404();
}

// Output Page
Plugin::Trigger ('activate.before_render');
$view->Render ('activate.tpl');