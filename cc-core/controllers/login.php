<?php

// Establish page variables, objects, arrays, etc
View::InitView ('login');
Plugin::triggerEvent('login.start');

// Verify if user is logged in
$userService = new UserService();
View::$vars->loggedInUser = $userService->loginCheck();

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
                Plugin::triggerEvent('login.remember_me');
            }
            Plugin::triggerEvent('login.login');
            header ('Location: ' . HOST . '/myaccount/');

        } else {
            View::$vars->message = Language::GetText('error_invalid_login');
            View::$vars->message_type = 'errors';
            View::$vars->login_submit = NULL;
        }

    } else {
        View::$vars->message = Language::GetText('error_general');
        View::$vars->message_type = 'errors';
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
            Plugin::triggerEvent('login.password_reset');
        } else {
            View::$vars->message = Language::GetText('error_no_users_email');
            View::$vars->message_type = 'errors';
        }

    } else {
        View::$vars->message = Language::GetText('error_email');
        View::$vars->message_type = 'errors';
    }
}

// Output Page
Plugin::triggerEvent('login.before_render');
View::Render ('login.tpl');