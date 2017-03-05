<?php

Plugin::triggerEvent('login.start', $this->view);

// Verify if user is logged in
$this->view->vars->loggedInUser = $this->authService->getAuthUser();
Functions::redirectIf(!$this->view->vars->loggedInUser, HOST . '/account/');

// Establish page variables, objects, arrays, etc
$userService = new UserService();
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

    // Validate form nonce token and submission speed
    if (
        !empty($_POST['nonce'])
        && !empty($_SESSION['formNonce'])
        && !empty($_SESSION['formTime'])
        && $_POST['nonce'] == $_SESSION['formNonce']
        && time() - $_SESSION['formTime'] >= 2
    ) {

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

            if ($user = $this->authService->validateCredentials(
                $this->view->vars->username,
                $this->view->vars->password
            )) {

                // Generate new session id
                session_regenerate_id(true);

                $this->authService->login($user);

                // Update user's last login date
                $user->lastLogin = gmdate(DATE_FORMAT);
                $userMapper = new \UserMapper();
                $userMapper->save($user);

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

    } else {
        $this->view->vars->message = Language::getText('invalid_session');
        $this->view->vars->message_type = 'errors';
    }
}

// Handle forgot login form
if (isset($_POST['submitted_forgot'])) {

    $this->view->vars->forgot_submit = true;

    // Validate form nonce token and submission speed
    if (
        !empty($_POST['nonce'])
        && !empty($_SESSION['formNonce'])
        && !empty($_SESSION['formTime'])
        && $_POST['nonce'] == $_SESSION['formNonce']
        && time() - $_SESSION['formTime'] >= 2
    ) {

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
                $mailer = new Mailer($config);
                $mailer->setTemplate ('forgot_password', $replacements);
                $mailer->Send ($user->email);
            } else {
                $this->view->vars->message = Language::getText('error_no_users_email');
                $this->view->vars->message_type = 'errors';
            }

        } else {
            $this->view->vars->message = Language::getText('error_email');
            $this->view->vars->message_type = 'errors';
        }

    } else {
        $this->view->vars->message = Language::getText('invalid_session');
        $this->view->vars->message_type = 'errors';
    }
}

// Set redirect location in form if requested
if (!empty($_GET['redirect'])) {
    $this->view->vars->redirect = trim($_GET['redirect']);
}

// Display session expired message
if (isset($_GET['session-expired'])) {
    $this->view->vars->message = Language::getText('session_expired');
    $this->view->vars->message_type = 'errors';
}

// Generate new form nonce
$this->view->vars->formNonce = md5(uniqid(rand(), true));
$_SESSION['formNonce'] = $this->view->vars->formNonce;
$_SESSION['formTime'] = time();

Plugin::triggerEvent('login.end', $this->view);
