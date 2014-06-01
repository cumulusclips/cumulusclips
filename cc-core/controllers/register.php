<?php

Plugin::Trigger ('register.start');

// Verify if user is logged in
$userService = new UserService();
$this->view->vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf(!$this->view->vars->loggedInUser, HOST . '/myaccount/');

// Establish page variables, objects, arrays, etc
$password = null;
$this->view->vars->message = null;
$this->view->vars->data = array ();
$this->view->vars->errors = array();
$token = null;



/***********************
Handle form if submitted
***********************/

if (isset($_GET['submitted'])) {
    
    if (
        !empty($_POST['token'])
        && !empty($_SESSION['formToken'])
        && $_POST['token'] == $_SESSION['formToken']
    ) {
        // Validate Username
        if (!empty($_POST['username']) && preg_match('/[a-z0-9]+/i', $_POST['username'])) {
            if (!User::Exist(array('username' => $_POST['username']))) {
                $this->view->vars->data['username'] = $_POST['username'];
            } else {
                $this->view->vars->errors['username'] = Language::GetText('error_username_unavailable');
            }
        } else {
            $this->view->vars->errors['username'] = Language::GetText('error_username');
        }

        // Validate password
        if (!empty($_POST['password'])) {
            $password = trim($_POST['password']);
        } else {
            $this->view->vars->errors['password'] = Language::GetText('error_password');
        }

        // Validate password confirm
        if (!empty($_POST['confirm'])) {
            if (isset($password) && $password == $_POST['confirm']) {
                $this->view->vars->data['password'] = $password;
            } else {
                $this->view->vars->errors['match'] = Language::GetText('error_password_match');
            }
        } else {
            $this->view->vars->errors['password_confirm'] = Language::GetText('error_password_confirm');
        }

        // Validate email
        if (!empty($_POST['email']) && preg_match('/^[a-z0-9][a-z0-9\._-]+@[a-z0-9][a-z0-9\.-]+\.[a-z0-9]{2,4}$/i', $_POST['email'])) {
            if (!User::Exist(array('email' => $_POST['email']))) {
                $this->view->vars->data['email'] = trim($_POST['email']);
            } else {
                $this->view->vars->errors['email'] = Language::GetText('error_email_unavailable');
            }
        } else {
            $this->view->vars->errors['email'] = Language::GetText('error_email');
        }   
        
    } else {
        $this->view->vars->errors['token'] = 'Invalid security token';
    }

    ### Create user if no errors were found
    if (empty ($this->view->vars->errors)) {

        $this->view->vars->data['confirm_code'] = User::CreateToken();
        $this->view->vars->data['status'] = 'new';
        $this->view->vars->data['password'] = md5 ($this->view->vars->data['password']);

        Plugin::Trigger ('register.before_create');
        User::Create ($this->view->vars->data);
        $this->view->vars->message = Language::GetText('success_registered');
        $this->view->vars->message_type = 'success';

        $replacements = array (
            'confirm_code' => $this->view->vars->data['confirm_code'],
            'host' => HOST,
            'sitename' => $config->sitename
        );
        $mail = new Mail();
        $mail->LoadTemplate ('welcome', $replacements);
        $mail->Send ($this->view->vars->data['email']);
        Plugin::Trigger ('register.create');
        unset ($this->view->vars->data);

    } else {
        $this->view->vars->message = Language::GetText('errors_below');
        $this->view->vars->message .= '<br /><br /> - ' . implode ('<br /> - ', $this->view->vars->errors);
        $this->view->vars->message_type = 'errors';
    }

}

// Generate new form token
$token = md5(uniqid(rand(), true));
$this->view->vars->token = $token;
$_SESSION['formToken'] = $token;

Plugin::Trigger ('register.before_render');