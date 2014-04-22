<?php

// Establish page variables, objects, arrays, etc
$view->InitView ('register');
Plugin::Trigger ('register.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf(!$view->vars->loggedInUser, HOST . '/myaccount/');
$password = null;
$view->vars->message = null;
$view->vars->data = array ();
$view->vars->errors = array();
$token = null;



/***********************
Handle form if submitted
***********************/

if (isset($_GET['submitted'])) {
    
    if (!empty($_SESSION['formToken'])) {
        
        // Retrieve token from original rendering
        $token = $_SESSION['formToken'];
        
        // Validate Username
        $usernameField = md5($token . 'username');
        if (!empty($_POST[$usernameField]) && preg_match('/[a-z0-9]+/i', $_POST[$usernameField])) {
            if (!User::Exist(array('username' => $_POST[$usernameField]))) {
                $view->vars->data['username'] = $_POST[$usernameField];
            } else {
                $view->vars->errors['username'] = Language::GetText('error_username_unavailable');
            }
        } else {
            $view->vars->errors['username'] = Language::GetText('error_username');
        }

        // Validate password
        $passwordField = md5($token . 'password');
        if (!empty($_POST[$passwordField])) {
            $password = trim($_POST[$passwordField]);
        } else {
            $view->vars->errors['password'] = Language::GetText('error_password');
        }

        // Validate password confirm
        $confirmField = md5($token . 'confirm');
        if (!empty($_POST[$confirmField])) {
            if (isset($password) && $password == $_POST[$confirmField]) {
                $view->vars->data['password'] = $password;
            } else {
                $view->vars->errors['match'] = Language::GetText('error_password_match');
            }
        } else {
            $view->vars->errors['password_confirm'] = Language::GetText('error_password_confirm');
        }

        // Validate email
        $emailField = md5($token . 'email');
        if (!empty($_POST[$emailField]) && preg_match('/^[a-z0-9][a-z0-9\._-]+@[a-z0-9][a-z0-9\.-]+\.[a-z0-9]{2,4}$/i', $_POST[$emailField])) {
            if (!User::Exist(array('email' => $_POST[$emailField]))) {
                $view->vars->data['email'] = trim($_POST[$emailField]);
            } else {
                $view->vars->errors['email'] = Language::GetText('error_email_unavailable');
            }
        } else {
            $view->vars->errors['email'] = Language::GetText('error_email');
        }   
        
    } else {
        $view->vars->errors['token'] = 'Invalid security token';
    }

    ### Create user if no errors were found
    if (empty ($view->vars->errors)) {

        $view->vars->data['confirm_code'] = User::CreateToken();
        $view->vars->data['status'] = 'new';
        $view->vars->data['password'] = md5 ($view->vars->data['password']);

        Plugin::Trigger ('register.before_create');
        User::Create ($view->vars->data);
        $view->vars->message = Language::GetText('success_registered');
        $view->vars->message_type = 'success';

        $replacements = array (
            'confirm_code' => $view->vars->data['confirm_code'],
            'host' => HOST,
            'sitename' => $config->sitename
        );
        $mail = new Mail();
        $mail->LoadTemplate ('welcome', $replacements);
        $mail->Send ($view->vars->data['email']);
        Plugin::Trigger ('register.create');
        unset ($view->vars->data);

    } else {
        $view->vars->message = Language::GetText('errors_below');
        $view->vars->message .= '<br /><br /> - ' . implode ('<br /> - ', $view->vars->errors);
        $view->vars->message_type = 'errors';
    }

}

// Generate new form token
$token = md5(uniqid(rand(), true));
$view->vars->usernameField = md5($token . 'username');
$view->vars->passwordField = md5($token . 'password');
$view->vars->confirmField = md5($token . 'confirm');
$view->vars->emailField = md5($token . 'email');
$_SESSION['formToken'] = $token;

// Output Page
Plugin::Trigger ('register.before_render');
$view->Render ('register.tpl');