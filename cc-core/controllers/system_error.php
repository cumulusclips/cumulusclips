<?php

// Establish page variables, objects, arrays, etc
View::InitView('system_error');
Plugin::Trigger('system_error.start');

// Verify if user is logged in
$userService = new UserService();
View::$vars->loggedInUser = $userService->loginCheck();

// Output Page
Plugin::Trigger('system_error.before_render');
View::Render('system_error.tpl');