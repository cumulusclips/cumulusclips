<?php

$this->view->options->disableView = true;

// Verify if user is logged in
$userService = new UserService();
if ($userService->loginCheck()) {
    $userService->logout(); // Plugin Hook is within method
    $_SESSION['logout'] = true;
    header('Location: ' . HOST . '/');
} else {
    header('Location: ' . HOST . '/account/');
}