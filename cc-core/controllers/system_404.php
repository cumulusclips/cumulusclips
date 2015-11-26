<?php

Plugin::triggerEvent('system_404.start');

// Verify if user is logged in
$userService = new UserService();
$this->view->vars->loggedInUser = $userService->loginCheck();

header ("HTTP/1.0 404 Not Found");
Plugin::triggerEvent('system_404.end');