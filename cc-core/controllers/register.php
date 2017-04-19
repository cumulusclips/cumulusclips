<?php

Plugin::triggerEvent('register.start');

// Verify if user registrations are enabled
$config = Registry::get('config');
if (!$config->enableRegistrations) App::throw404();

// Verify if user is logged in
$this->view->vars->loggedInUser = $this->authService->getAuthUser();
Functions::redirectIf(!$this->view->vars->loggedInUser, HOST . '/account/');

// Establish page variables, objects, arrays, etc
$userService = new UserService();
$password = null;
$this->view->vars->message = null;
$this->view->vars->data = array ();
$this->view->vars->errors = array();
$token = null;
$userMapper = new UserMapper();

// Handle form if submitted
if (isset($_POST['submitted'])) {

    // Validate form nonce token and submission speed
    if (
        !empty($_POST['nonce'])
        && !empty($_SESSION['formNonce'])
        && !empty($_SESSION['formTime'])
        && $_POST['nonce'] == $_SESSION['formNonce']
        && time() - $_SESSION['formTime'] >= 2
    ) {

        $this->view->vars->user = new User();

        // Validate Username
        if (!empty($_POST['username']) && preg_match('/^[a-z0-9]+$/i', $_POST['username'])) {
            if (!$userMapper->getUserByUsername($_POST['username'])) {
                $this->view->vars->user->username = $_POST['username'];
            } else {
                $this->view->vars->errors['username'] = Language::getText('error_username_unavailable');
            }
        } else {
            $this->view->vars->errors['username'] = Language::getText('error_username');
        }

        // Validate password
        if (!empty($_POST['password'])) {
            $password = trim($_POST['password']);
        } else {
            $this->view->vars->errors['password'] = Language::getText('error_password');
        }

        // Validate password confirm
        if (!empty($_POST['confirm'])) {
            if (isset($password) && $password == $_POST['confirm']) {
                $this->view->vars->user->password = $password;
            } else {
                $this->view->vars->errors['match'] = Language::getText('error_password_match');
            }
        } else {
            $this->view->vars->errors['password_confirm'] = Language::getText('error_password_confirm');
        }

        // Validate email
        if (!empty($_POST['email']) && preg_match('/^[a-z0-9][a-z0-9\._-]+@[a-z0-9][a-z0-9\.-]+\.[a-z0-9]{2,4}$/i', $_POST['email'])) {
            if (!$userMapper->getUserByCustom(array('email' => $_POST['email']))) {
                $this->view->vars->user->email = trim($_POST['email']);
            } else {
                $this->view->vars->errors['email'] = Language::getText('error_email_unavailable');
            }
        } else {
            $this->view->vars->errors['email'] = Language::getText('error_email');
        }

        $this->view->vars->errors = Plugin::triggerFilter('register.validation', $this->view->vars->errors, $_POST);

        // Create user if no errors were found
        if (empty ($this->view->vars->errors)) {
            // Create new user
            $newUser = $userService->create($this->view->vars->user);

            // Send welcome email
            $config = Registry::get('config');
            $replacements = array (
                'confirm_code' => $newUser->confirmCode,
                'host' => HOST,
                'sitename' => $config->sitename
            );
            $mailer = new Mailer($config);
            $mailer->setTemplate('welcome', $replacements);
            $mailer->send($newUser->email);

            // Prepare message
            unset($this->view->vars->user);
            $this->view->vars->message = Language::getText('success_registered');
            $this->view->vars->message_type = 'success';
        } else {
            $this->view->vars->message = Language::getText('errors_below');
            $this->view->vars->message .= '<br><br> - ' . implode('<br> - ', $this->view->vars->errors);
            $this->view->vars->message_type = 'errors';
        }

    } else {
        $this->view->vars->message = Language::getText('invalid_session');
        $this->view->vars->message_type = 'errors';
    }
}

// Generate new form nonce
$this->view->vars->formNonce = md5(uniqid(rand(), true));
$_SESSION['formNonce'] = $this->view->vars->formNonce;
$_SESSION['formTime'] = time();

Plugin::triggerEvent('register.end');
