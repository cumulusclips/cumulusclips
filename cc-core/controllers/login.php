<?php

### Created on March 7, 2009
### Created by Miguel A. Hurtado
### This script allows users to login


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('EmailTemplate');
View::InitView();
Plugin::Trigger ('login.start');


// Establish page variables, objects, arrays, etc
View::LoadPage ('login');
View::$vars->logged_in = User::LoginCheck();
if (View::$vars->logged_in) header ('Location: ' . HOST . '/myaccount/');
View::$vars->username = NULL;
View::$vars->password = NULL;
View::$vars->message = NULL;
View::$vars->message_type = NULL;
View::$vars->login_submit = NULL;
View::$vars->forgot_submit = NULL;



// User Requested forgot password
if (isset ($_GET['action']) && $_GET['action'] == 'forgot') {
    View::$vars->forgot_submit = TRUE;
}





/*****************************
Handle login form if submitted
*****************************/

if (isset ($_POST['submitted_login'])) {

	
    View::$vars->login_submit = TRUE;

    // validate username
    if (!empty ($_POST['username']) && !ctype_space ($_POST['username'])) {
        View::$vars->username = htmlspecialchars ($_POST['username']);
    }

    // validate password
    if (!empty ($_POST['password']) && !ctype_space ($_POST['password'])) {
        View::$vars->password = $_POST['password'];
    }
	
    // login if no errors were found
    if (View::$vars->username && View::$vars->password) {

        if (User::Login (View::$vars->username, View::$vars->password)) {

            if (isset ($_POST['remember']) && $_POST['remember'] == 'TRUE') {
                setcookie ('username',View::$vars->username,time()+60*60*24*9999,'/','',0);
                setcookie ('password',View::$vars->password,time()+60*60*24*9999,'/','',0);
            }
            header ('Location: ' . HOST . '/myaccount/');

        } else {
            View::$vars->message = Language::GetText('error_invalid_login');
            View::$vars->message_type = 'error';
            View::$vars->login_submit = NULL;
        }

    } else {
        View::$vars->message = Language::GetText('error_general');
        View::$vars->message_type = 'error';
    }
	
}





/***********************
Handle forgot login form
***********************/

if (isset ($_POST['submitted_forgot'])) {
	
    View::$vars->forgot_submit = TRUE;

    // validate email
    $string = '/^[a-z0-9][a-z0-9\._-]+@[a-z0-9][a-z0-9\.-]+[a-z0-9]{2,4}$/i';
    if (!empty ($_POST['email']) && !ctype_space ($_POST['email']) && preg_match ($string,$_POST['email'])) {

        $data = array ('email' => $_POST['email']);
        $id = User::Exist ($data);
        if ($id) {

            $user = new User ($id);
            $user->ResetPassword();
            View::$vars->message = Language::GetText('success_login_sent');
            View::$vars->message_type = 'success';
            View::$vars->forgot_submit = NULL;

            $Msg = array (
                'username'  => $user->username,
                'password' => $user->password
            );
            $template = new EmailTemplate ('/forgot_password.htm');
            $template->Replace ($Msg);
            $template->Send ($user->email);

        } else {
            View::$vars->message = Language::GetText('error_no_users_email');
            View::$vars->message_type = 'error';
        }

    } else {
        View::$vars->message = Language::GetText('error_email');
        View::$vars->message_type = 'error';
    }
	
}


// Output Page
Plugin::Trigger ('login.pre_render');
View::Render ('login.tpl');

?>