<?php

Plugin::triggerEvent('upload_video.start');

// Verify if user registrations are enabled
$config = Registry::get('config');
if (!$config->enableUserUploads) App::throw404();

// Verify if user is logged in
$this->authService->enforceAuth();
$this->view->vars->loggedInUser = $this->authService->getAuthUser();

// Establish page variables, objects, arrays, etc
$tempFilePrefix = UPLOAD_PATH . '/temp/' . $this->view->vars->loggedInUser->userId . '-video-';
App::enableUploadsCheck();
unset($_SESSION['upload']);

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

        // Validate uploaded video file
        if (!empty($_POST['upload']['temp'])
            && \App::isValidUpload($_POST['upload']['temp'], $this->view->vars->loggedInUser, 'video')
        ) {
            // Store uploaded file into session
            $_SESSION['upload'] = (object) array(
                'temp' => $_POST['upload']['temp'],
                'time' => time()
            );

            // Move to video information page
            header('Location: ' . HOST . '/account/upload/info/');
            exit();

        } else {
            $this->view->vars->message = Language::getText('error_video_upload');
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

Plugin::triggerEvent('upload_video.end');
