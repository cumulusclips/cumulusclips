<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Mail');


// Establish page variables, objects, arrays, etc
View::InitView ('login');
Plugin::Trigger ('login.start');
View::$vars->logged_in = User::LoginCheck();
Functions::RedirectIf (!View::$vars->logged_in, HOST . '/myaccount/');
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
                Plugin::Trigger ('login.remember_me');
            }
            Plugin::Trigger ('login.login');
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
        $user_id = User::Exist ($data);
        if ($user_id) {

            $user = new User ($user_id);
            $new_password = $user->ResetPassword();
            View::$vars->message = Language::GetText('success_login_sent');
            View::$vars->message_type = 'success';
            View::$vars->forgot_submit = NULL;

            $replacements = array (
                'sitename'  => $config->sitename,
                'username'  => $user->username,
                'password'  => $new_password
            );
            $mail = new Mail();
            $mail->LoadTemplate ('forgot_password', $replacements);
            $mail->Send ($user->email);
            Plugin::Trigger ('login.password_reset');

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
Plugin::Trigger ('login.before_render');
View::Render ('login.tpl');

?>