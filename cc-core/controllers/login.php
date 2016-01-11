<?php

Plugin::triggerEvent('login.start');

// Verify if user is logged in
$userService = new UserService();
$this->view->vars->loggedInUser = $userService->loginCheck();
Functions::redirectIf(!$this->view->vars->loggedInUser, HOST . '/account/');

$config = Registry::get('config');
$this->view->vars->username = null;
$this->view->vars->password = null;
$this->view->vars->redirect = null;
$this->view->vars->message = null;
$this->view->vars->message_type = null;
$this->view->vars->login_submit = null;
$this->view->vars->forgot_submit = null;

// Handle login form if submitted
if (isset($_POST['submitted_login'])) {

    $this->view->vars->login_submit = true;

    // Validate redirect location
    if (!empty($_POST['redirect'])) {

        $redirectUrlParts = parse_url(urldecode($_POST['redirect']));
        $this->view->vars->redirect = HOST;

        // Append redirect url path
        if (!empty($redirectUrlParts['path'])) {
            $this->view->vars->redirect .= '/' . trim($redirectUrlParts['path'], '/') . '/';
        }
        
        // Append redirect url query
        if (!empty($redirectUrlParts['query'])) {
            $this->view->vars->redirect .= '?' . $redirectUrlParts['query'];
        }

        // Append redirect url fragment
        if (!empty($redirectUrlParts['fragment'])) {
            $this->view->vars->redirect .= '#' . $redirectUrlParts['fragment'];
        }
    }

    // validate username
    if (!empty($_POST['username'])) {
        $this->view->vars->username = trim($_POST['username']);
    }

    // validate password
    if (!empty($_POST['password'])) {
        $this->view->vars->password = $_POST['password'];
    }
	
    // login if no errors were found
    if ($this->view->vars->username && $this->view->vars->password) {

        if ($userService->login($this->view->vars->username, $this->view->vars->password)) {
            // Detect if post-login redirect was requested otherwise redirect to account index
            $url = ($this->view->vars->redirect) ? $this->view->vars->redirect : HOST . '/account/';
            $url = Plugin::triggerFilter('login_redirect', $url);
            header('Location: ' . $url);
        } else {
            $this->view->vars->message = Language::getText('error_invalid_login');
            $this->view->vars->message_type = 'errors';
        }

    } else {
        $this->view->vars->message = Language::getText('error_general');
        $this->view->vars->message_type = 'errors';
    }
}

// Handle forgot login form
if (isset($_POST['submitted_forgot'])) {
	
    $this->view->vars->forgot_submit = true;

    // validate email
    $string = '/^[a-z0-9][a-z0-9\._-]+@[a-z0-9][a-z0-9\.-]+[a-z0-9]{2,4}$/i';
    if (!empty($_POST['email']) && preg_match($string,$_POST['email'])) {

        $userMapper = new UserMapper();
        $user = $userMapper->getUserByCustom(array('email' => $_POST['email'], 'status' => 'active'));
        if ($user) {
            $new_password = $userService->resetPassword($user);
            $this->view->vars->message = Language::getText('success_login_sent');
            $this->view->vars->message_type = 'success';

            $replacements = array (
                'sitename'  => $config->sitename,
                'username'  => $user->username,
                'password'  => $new_password
            );
            $mailer = new Mailer();
            $mailer->LoadTemplate ('forgot_password', $replacements);
            $mailer->Send ($user->email);
        } else {
            $this->view->vars->message = Language::getText('error_no_users_email');
            $this->view->vars->message_type = 'errors';
        }

    } else {
        $this->view->vars->message = Language::getText('error_email');
        $this->view->vars->message_type = 'errors';
    }
}

// Set redirect location in form if requested
if (!empty($_GET['redirect'])) {
    $this->view->vars->redirect = trim($_GET['redirect']);
}

Plugin::triggerEvent('login.end');