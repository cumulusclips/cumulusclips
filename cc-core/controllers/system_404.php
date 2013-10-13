<?php

// Establish page variables, objects, arrays, etc
View::InitView ('system_404');
Plugin::Trigger ('system_404.start');

// Verify if user is logged in
$userService = new UserService();
View::$vars->loggedInUser = $userService->loginCheck();

// Output page
header ("HTTP/1.0 404 Not Found");
Plugin::Trigger ('system_404.before_render');
View::Render ('system_404.tpl');