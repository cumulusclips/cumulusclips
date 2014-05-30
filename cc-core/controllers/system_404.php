<?php

Plugin::Trigger ('system_404.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();

header ("HTTP/1.0 404 Not Found");
Plugin::Trigger ('system_404.before_render');