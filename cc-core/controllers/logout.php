<?php

$this->view->options->disableView = true;

// Verify if user is logged in
if ($this->authService->getAuthUser()) {

    $this->authService->logout();

    // Generate new session id
    session_regenerate_id(true);

    $_SESSION['logout'] = true;
    header('Location: ' . HOST . '/');

} else {
    header('Location: ' . HOST . '/account/');
}