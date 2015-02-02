<?php

Plugin::triggerEvent('mobile_languages.start');

// Verify if user is logged in
$userService = new UserService();
$this->view->vars->loggedInUser = $userService->loginCheck();

Plugin::triggerEvent('mobile_languages.end');