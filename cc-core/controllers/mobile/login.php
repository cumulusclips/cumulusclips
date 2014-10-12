<?php

// Verify if user is logged in
$userService = new UserService();
$this->view->vars->loggedInUser = $userService->loginCheck();
Functions::redirectIf(!$this->view->vars->loggedInUser, HOST . '/m/');

// Establish page variables, objects, arrays, etc
$this->view->vars->username = null;
$this->view->vars->password = null;
$this->view->vars->message = null;
$this->view->vars->message_type = null;
$this->view->vars->login_submit = null;
$this->view->vars->forgot_submit = null;

// User Requested forgot password
if (isset ($_GET['action']) && $_GET['action'] == 'forgot') {
    $this->view->vars->forgot_submit = true;
}

/*****************************
Handle login form if submitted
*****************************/

if (isset ($_POST['submitted_login'])) {

	
    $this->view->vars->login_submit = true;

    // validate username
    if (!empty ($_POST['username']) && !ctype_space ($_POST['username'])) {
        $this->view->vars->username = trim($_POST['username']);
    }

    // validate password
    if (!empty ($_POST['password']) && !ctype_space ($_POST['password'])) {
        $this->view->vars->password = $_POST['password'];
    }
	
    // login if no errors were found
    if ($this->view->vars->username && $this->view->vars->password) {

        if ($userService->login($this->view->vars->username, $this->view->vars->password)) {

            if (isset ($_POST['remember']) && $_POST['remember'] == 'true') {
                setcookie ('username',$this->view->vars->username,time()+60*60*24*9999,'/','',0);
                setcookie ('password',$this->view->vars->password,time()+60*60*24*9999,'/','',0);
            }
            header ('Location: ' . HOST . '/account/');

        } else {
            $this->view->vars->message = Language::GetText('error_invalid_login');
            $this->view->vars->message_type = 'errors';
            $this->view->vars->login_submit = null;
        }

    } else {
        $this->view->vars->message = Language::GetText('error_general');
        $this->view->vars->message_type = 'errors';
    }
}

/***********************
Handle forgot login form
***********************/

if (isset ($_POST['submitted_forgot'])) {
	
    $this->view->vars->forgot_submit = true;

    // validate email
    $string = '/^[a-z0-9][a-z0-9\._-]+@[a-z0-9][a-z0-9\.-]+[a-z0-9]{2,4}$/i';
    if (!empty ($_POST['email']) && !ctype_space ($_POST['email']) && preg_match ($string,$_POST['email'])) {

        $userMapper = new UserMapper();
        $user = $userMapper->getUserByCustom(array('email' => $_POST['email'], 'status' => 'active'));
        if ($user) {
            $new_password = $userService->resetPassword($user);
            $this->view->vars->message = Language::GetText('success_login_sent');
            $this->view->vars->message_type = 'success';
            $this->view->vars->forgot_submit = null;

            $replacements = array (
                'sitename'  => $config->sitename,
                'username'  => $user->username,
                'password'  => $new_password
            );
            $mail = new Mail();
            $mail->LoadTemplate ('forgot_password', $replacements);
            $mail->Send ($user->email);
        } else {
            $this->view->vars->message = Language::GetText('error_no_users_email');
            $this->view->vars->message_type = 'errors';
        }

    } else {
        $this->view->vars->message = Language::GetText('error_email');
        $this->view->vars->message_type = 'errors';
    }
}