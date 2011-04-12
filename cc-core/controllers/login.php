<?php

### Created on March 7, 2009
### Created by Miguel A. Hurtado
### This script allows users to login


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('EmailTemplate');
View::InitView();


// Establish page variables, objects, arrays, etc
View::$vars->logged_in = User::LoginCheck();
if (View::$vars->logged_in) header ('Location: ' . HOST . '/myaccount/');
View::$vars->page_title = 'Cumulus - Login to your Account';
View::$vars->username = NULL;
View::$vars->password = NULL;
View::$vars->message = NULL;
View::$vars->login_submit = NULL;
View::$vars->forgot_submit = NULL;
View::$vars->forgot_outside = NULL;



// User Requested forgot password
if (isset ($_GET['action']) && $_GET['action'] == 'forgot') {
    View::$vars->forgot_outside = TRUE;
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
            View::$vars->message = '<div id="error">Invalid login! Please make sure you typed your username and password correctly and try again.</div>';
            View::$vars->login_submit = NULL;
        }

    } else {
//    exit();
        View::$vars->message = '<div id="error">Errors were found! Please try again.</div>';
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
            View::$vars->message = '<div id="success">We have sent you your login information. Please check your email.</div>';
            View::$vars->forgot_submit = NULL;

            $Msg = array (
                'username'  => $user->username,
                'password' => $user->password
            );
            $template = new EmailTemplate ('/forgot_password.htm');
            $template->Replace ($Msg);
            $template->Send ($user->email);

        } else {
            View::$vars->message = '<div id="error">No users were found with that email! Please try again.</div>';
        }

    } else {
        View::$vars->message = '<div id="error">Errors were found! Please try again.</div>';
    }
	
}


// Output Page
View::Render ('login.tpl');

?>