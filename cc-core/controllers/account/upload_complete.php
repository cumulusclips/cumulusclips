<?php

Plugin::triggerEvent('upload_complete.start');

// Verify if user registrations are enabled
$config = Registry::get('config');
if (!$config->enableUserUploads) App::throw404();

// Verify if user is logged in
$this->authService->enforceAuth();
$this->authService->enforceTimeout(true);
$this->view->vars->loggedInUser = $this->authService->getAuthUser();

// Establish page variables, objects, arrays, etc
App::enableUploadsCheck();

// Verify user completed upload process
if (isset($_SESSION['upload']->videoId)) {
    unset($_SESSION['upload']);
} else {
    header('Location: ' . HOST . '/account/upload/info/');
    exit();
}

Plugin::triggerEvent('upload_complete.end');
