<?php

// Establish page variables, objects, arrays, etc
$view->InitView('system_error');
Plugin::Trigger('system_error.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();

// Output Page
Plugin::Trigger('system_error.before_render');
$view->Render('system_error.tpl');