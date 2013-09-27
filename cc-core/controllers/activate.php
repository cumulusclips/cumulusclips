<?php

// Establish page variables, objects, arrays, etc
View::InitView ('activate');
Plugin::Trigger ('activate.start');

View::$vars->logged_in = UserService::LoginCheck();
$userMapper = new UserMapper();
if (View::$vars->logged_in) {
    $userMapper->getUserById(View::$vars->logged_in);
}
Functions::RedirectIf (!View::$vars->logged_in, HOST . '/myaccount/');
View::$vars->message = null;

### Verify token was provided
if (isset ($_GET['token'])) {
	
    $token = $_GET['token'];
    $user = $userMapper->getUserByCustom(array('confirm_code' => $token, 'status' => 'new'));
    if ($user) {
        $userService = new UserService();
        $userService->approve($user, 'activate');
        if (Settings::Get('auto_approve_users') == 1) {
            View::$vars->message = Language::GetText ('activate_success', array ('host' => HOST));
            $_SESSION['user_id'] = $user->userId;
        } else {
            View::$vars->message = Language::GetText ('activate_approve');
        }
        View::$vars->message_type = 'success';
        Plugin::Trigger ('activate.activate');
    } else {
        View::$vars->message = Language::GetText ('activate_error', array ('host' => HOST));
        View::$vars->message_type = 'error';
    }
	
} else {
    App::Throw404();
}

// Output Page
Plugin::Trigger ('activate.before_render');
View::Render ('activate.tpl');