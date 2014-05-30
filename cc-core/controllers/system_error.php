<?php

Plugin::Trigger('system_error.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();

Plugin::Trigger('system_error.before_render');