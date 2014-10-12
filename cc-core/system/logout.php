<?php

// Verify if user is logged in
$userService = new UserService();
if ($userService->loginCheck()) {
    $userService->logout(); // Plugin Hook is within method
    header ('Location: ' . HOST . '/');
} else {
    header ('Location: ' . HOST . '/account/');
}