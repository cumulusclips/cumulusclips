<?php

Plugin::triggerEvent('mobile_languages.start');

// Verify if user is logged in
$userService = new UserService();
$this->view->vars->loggedInUser = $userService->loginCheck();

// Establish page variables, objects, arrays, etc
$videoMapper = new VideoMapper();
$db = Registry::get('db');



Plugin::triggerEvent('mobile_languages.end');