<?php

### Created on March 14, 2009
### Created by Miguel A. Hurtado
### This script confirms a users account


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
View::InitView();


// Establish page variables, objects, arrays, etc
View::LoadPage ('activate');
Plugin::Trigger ('activate.start');
View::$vars->logged_in = User::LoginCheck();
if (View::$vars->logged_in) header ('Location: ' . HOST . '/myaccount/');
View::$vars->Error = NULL;
View::$vars->Success = NULL;



### Verify token was provided
if (isset ($_GET['token'])) {
	
    $token = $_GET['token'];
    $data = array ('confirm_code' => $token, 'account_status' => 'Pending Confirm');
    $id = User::Exist ($data);
    if ($id) {
        $user = new User ($id);
        $user->Activate();
        View::$vars->success = Language::GetText('activate_text_success', array ('link' => HOST . '/myaccount/'));
        User::Login ($user->username, $user->password);
        Plugin::Trigger ('activate.activate');
    } else {
        View::$vars->error_msg = Language::GetText('activate_text_error', array ('link' => HOST . '/login/forgot/'));
    }
	
} else {
    App::Throw404();
}


// Output Page
Plugin::Trigger ('activate.before_render');
View::Render ('activate.tpl');

?>