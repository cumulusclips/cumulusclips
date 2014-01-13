<?php

// Establish page variables, objects, arrays, etc
$view->InitView ('login');
Plugin::triggerEvent('login.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();
Functions::redirectIf(!$view->vars->loggedInUser, HOST . '/myaccount/');

$view->vars->username = null;
$view->vars->password = null;
$view->vars->message = null;
$view->vars->message_type = null;
$view->vars->login_submit = null;
$view->vars->forgot_submit = null;

// User Requested forgot password
if (isset ($_GET['action']) && $_GET['action'] == 'forgot') {
    $view->vars->forgot_submit = true;
}

/*****************************
Handle login form if submitted
*****************************/

if (isset ($_POST['submitted_login'])) {

	
    $view->vars->login_submit = true;

    // validate username
    if (!empty ($_POST['username']) && !ctype_space ($_POST['username'])) {
        $view->vars->username = trim($_POST['username']);
    }

    // validate password
    if (!empty ($_POST['password']) && !ctype_space ($_POST['password'])) {
        $view->vars->password = $_POST['password'];
    }
	
    // login if no errors were found
    if ($view->vars->username && $view->vars->password) {

        if ($userService->login($view->vars->username, $view->vars->password)) {

            if (isset ($_POST['remember']) && $_POST['remember'] == 'true') {
                setcookie ('username',$view->vars->username,time()+60*60*24*9999,'/','',0);
                setcookie ('password',$view->vars->password,time()+60*60*24*9999,'/','',0);
                Plugin::triggerEvent('login.remember_me');
            }
            Plugin::triggerEvent('login.login');
            header ('Location: ' . HOST . '/myaccount/');

        } else {
            $view->vars->message = Language::GetText('error_invalid_login');
            $view->vars->message_type = 'errors';
            $view->vars->login_submit = null;
        }

    } else {
        $view->vars->message = Language::GetText('error_general');
        $view->vars->message_type = 'errors';
    }
}

/***********************
Handle forgot login form
***********************/

if (isset ($_POST['submitted_forgot'])) {
	
    $view->vars->forgot_submit = true;

    // validate email
    $string = '/^[a-z0-9][a-z0-9\._-]+@[a-z0-9][a-z0-9\.-]+[a-z0-9]{2,4}$/i';
    if (!empty ($_POST['email']) && !ctype_space ($_POST['email']) && preg_match ($string,$_POST['email'])) {

        $userMapper = new UserMapper();
        $user = $userMapper->getUserByCustom(array('email' => $_POST['email'], 'status' => 'active'));
        if ($user) {
            $new_password = $userService->resetPassword($user);
            $view->vars->message = Language::GetText('success_login_sent');
            $view->vars->message_type = 'success';
            $view->vars->forgot_submit = null;

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
            $view->vars->message = Language::GetText('error_no_users_email');
            $view->vars->message_type = 'errors';
        }

    } else {
        $view->vars->message = Language::GetText('error_email');
        $view->vars->message_type = 'errors';
    }
}

// Output Page
Plugin::triggerEvent('login.before_render');
$view->Render ('login.tpl');