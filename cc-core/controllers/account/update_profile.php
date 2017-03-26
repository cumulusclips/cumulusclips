<?php

Plugin::triggerEvent('update_profile.start');

// Verify if user is logged in
$this->authService->enforceAuth();
$this->authService->enforceTimeout(true);
$this->view->vars->loggedInUser = $this->authService->getAuthUser();

// Establish page variables, objects, arrays, etc
$userMapper = new UserMapper();
$this->view->vars->Errors = array();
$this->view->vars->message = null;
$tempFilePrefix = UPLOAD_PATH . '/temp/' . $this->view->vars->loggedInUser->userId . '-image-';

// Update profile if requested
if (isset($_POST['submitted'])) {

    // Validate form nonce token and submission speed
    if (
        !empty($_POST['nonce'])
        && !empty($_SESSION['formNonce'])
        && !empty($_SESSION['formTime'])
        && $_POST['nonce'] == $_SESSION['formNonce']
        && time() - $_SESSION['formTime'] >= 2
    ) {

        $this->view->vars->profile_submit = true;

        // Validate First Name
        if (!empty($this->view->vars->loggedInUser->firstName) && $_POST['first_name'] == '') {
            $this->view->vars->loggedInUser->firstName = '';
        } elseif (!empty($_POST['first_name'])) {
            $this->view->vars->loggedInUser->firstName = trim($_POST['first_name']);
        }

        // Validate Last Name
        if (!empty($this->view->vars->loggedInUser->lastName) && $_POST['last_name'] == '') {
            $this->view->vars->loggedInUser->lastName = '';
        } elseif (!empty($_POST['last_name'])) {
            $this->view->vars->loggedInUser->lastName = trim($_POST['last_name']);
        }

        // Validate Email
        if (!empty($_POST['email']) && preg_match('/^[a-z0-9][a-z0-9_\.\-]+@[a-z0-9][a-z0-9\.\-]+\.[a-z0-9]{2,4}$/i', $_POST['email'])) {
            $userCheck = $userMapper->getUserByCustom(array('email' => $_POST['email']));
            if (!$userCheck || $userCheck->email == $this->view->vars->loggedInUser->email) {
                $this->view->vars->loggedInUser->email = $_POST['email'];
            } else {
                $this->view->vars->Errors['email'] = Language::GetText('error_email_unavailable');
            }
        } else {
            $this->view->vars->Errors['email'] = Language::GetText('error_email');
        }

        // Validate Website
        if (!empty($_POST['website'])) {
            $website = $_POST['website'];
            if (preg_match ('/^(https?:\/\/)?[a-z0-9][a-z0-9\.-]+\.[a-z0-9]{2,4}.*$/i', $website, $matches)) {
                $website = (empty($matches[1]) ? 'http://' : '') . $website;
                $this->view->vars->loggedInUser->website = trim($website);
            } else {
                $this->view->vars->errors['website'] = Language::GetText('error_website_invalid');
            }
        } else {
            $this->view->vars->loggedInUser->website = '';
        }

        // Validate About Me
        if (!empty($this->view->vars->loggedInUser->aboutMe) && $_POST['about_me'] == '') {
            $this->view->vars->loggedInUser->aboutMe = '';
        } elseif (!empty($_POST['about_me'])) {
            $this->view->vars->loggedInUser->aboutMe = trim($_POST['about_me']);
        }

        // Update User if no errors were found
        if (empty ($this->view->vars->Errors)) {
            $this->view->vars->message = Language::GetText('success_profile_updated');
            $this->view->vars->message_type = 'success';
            $userMapper->save($this->view->vars->loggedInUser);
        } else {
            $this->view->vars->message = Language::GetText('errors_below');
            $this->view->vars->message .= '<br /><br /> - ' . implode ('<br /> - ', $this->view->vars->Errors);
            $this->view->vars->message_type = 'errors';
        }

    } else {
        $this->view->vars->message = Language::getText('invalid_session');
        $this->view->vars->message_type = 'errors';
    }
}

// Reset avatar if requested
if (!empty($_GET['action']) && $_GET['action'] == 'reset' && !empty($this->view->vars->loggedInUser->avatar)) {

    $this->view->vars->avatar_submit = true;

    $deleteResult = Avatar::delete($this->view->vars->loggedInUser->avatar);
    if ($deleteResult) {
        $this->view->vars->loggedInUser->avatar = null;
        $userMapper->save($this->view->vars->loggedInUser);
        $this->view->vars->message = Language::getText('success_avatar_reset');
        $this->view->vars->message_type = 'success';
    } else {
        $this->view->vars->message = Language::getText('error_avatar_reset');
        $this->view->vars->message_type = 'errors';
    }
}

// Update avatar if requested
if (
    isset($_POST['submitted_avatar'])
    && !empty($_POST['upload']['temp'])
    && \App::isValidUpload($_POST['upload']['temp'], $this->view->vars->loggedInUser, 'image')
) {

    // Validate form nonce token and submission speed
    if (
        !empty($_POST['nonce'])
        && !empty($_SESSION['formNonce'])
        && !empty($_SESSION['formTime'])
        && $_POST['nonce'] == $_SESSION['formNonce']
        && time() - $_SESSION['formTime'] >= 2
    ) {

        $this->view->vars->avatar_submit = true;
        $filename = Avatar::generateFilename();
        $avatar = UPLOAD_PATH . '/avatars/' . $filename . '.png';

        // Attempt to save avatar
        if (Avatar::saveAvatar($_POST['upload']['temp'], $filename)) {

            // Check for existing avatar
            if (!empty($this->view->vars->loggedInUser->avatar)) Avatar::delete($this->view->vars->loggedInUser->avatar);

            // Update User
            $userMapper = new UserMapper();
            $this->view->vars->loggedInUser->avatar = $filename . '.png';
            $userMapper->save($this->view->vars->loggedInUser);

            $this->view->vars->message = Language::getText('success_profile_updated');
            $this->view->vars->message_type = 'success';
            $userMapper->save($this->view->vars->loggedInUser);

        } else {
            $this->view->vars->message = Language::getText('error_upload_system', array ('host' => HOST));
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

Plugin::triggerEvent('update_profile.end');
