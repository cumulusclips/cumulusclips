<?php

### Created on March 14, 2009
### Created by Miguel A. Hurtado
### This script confirms a users account


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
View::InitView();


// Establish page variables, objects, arrays, etc
View::$vars->logged_in = User::LoginCheck();
if (View::$vars->logged_in) header ('Location: ' . HOST . '/myaccount/');
View::$vars->page_title = 'Techie Videos - Activate your Account';
View::$vars->Error = NULL;
View::$vars->Success = NULL;



### Verify token was provided
if (isset ($_GET['token'])) {
	
    $token = $_GET['token'];
    $data = array ('confirm_code' => $token, 'account_status' => 'Pending Confirm');
    $id = User::Exist ($data);
    if ($id) {
        $user = new User ($id);
        $user->ActivateUser();
        View::$vars->Success = true;
        User::Login ($user->username, $user->password);
    } else {
        View::$vars->Error =true;
    }
	
} else {
    header ('Location: ' . HOST);
}


// Output Page
View::Render ('confirm.tpl');

?>