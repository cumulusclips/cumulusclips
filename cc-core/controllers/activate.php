<?php

### Created on March 14, 2009
### Created by Miguel A. Hurtado
### This script confirms a users account


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');


// Establish page variables, objects, arrays, etc
View::InitView ('activate');
Plugin::Trigger ('activate.start');
View::$vars->logged_in = User::LoginCheck();
if (View::$vars->logged_in) header ('Location: ' . HOST . '/myaccount/');
View::$vars->message = null;



### Verify token was provided
if (isset ($_GET['token'])) {
	
    $token = $_GET['token'];
    $id = User::Exist (array ('confirm_code' => $token, 'status' => 'new'));
    if ($id) {
        $user = new User ($id);
        $user->Approve();
        View::$vars->message = Language::GetText ('activate_text_success', array ('link' => HOST . '/myaccount/'));
        View::$vars->message_type = 'success';
        User::Login ($user->username, $user->password);
        Plugin::Trigger ('activate.activate');
    } else {
        View::$vars->message = Language::GetText ('activate_text_error', array ('link' => HOST . '/login/forgot/'));
        View::$vars->message_type = 'error';
    }
	
} else {
    App::Throw404();
}


// Output Page
Plugin::Trigger ('activate.before_render');
View::Render ('activate.tpl');

?>