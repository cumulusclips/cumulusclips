<?php

// Establish page variables, objects, arrays, etc
View::InitView ('activate');
Plugin::Trigger ('activate.start');

View::$vars->logged_in = UserService::LoginCheck();
if (View::$vars->logged_in) {
    $userMapper = new UserMapper();
    $userMapper->getUserById(View::$vars->logged_in);
}
Functions::RedirectIf (!View::$vars->logged_in, HOST . '/myaccount/');
View::$vars->message = null;



### Verify token was provided
if (isset ($_GET['token'])) {
	
    $token = $_GET['token'];
    $id = User::Exist (array ('confirm_code' => $token, 'status' => 'new'));
    if ($id) {
        $user = new User ($id);
        $user->Approve ('activate');
        if (Settings::Get('auto_approve_users') == 1) {
            View::$vars->message = Language::GetText ('activate_success', array ('host' => HOST));
            $_SESSION['user_id'] = $user->user_id;
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