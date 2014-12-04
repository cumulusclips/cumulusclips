<?php

Plugin::triggerEvent('system_error.start');

// Verify if user is logged in
$userService = new UserService();
$this->view->vars->loggedInUser = $userService->loginCheck();

Plugin::triggerEvent('system_error.end');