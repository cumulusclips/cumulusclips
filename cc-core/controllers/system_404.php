<?php

// Establish page variables, objects, arrays, etc
$view->InitView ('system_404');
Plugin::Trigger ('system_404.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();

// Output page
header ("HTTP/1.0 404 Not Found");
Plugin::Trigger ('system_404.before_render');
$view->Render ('system_404.tpl');