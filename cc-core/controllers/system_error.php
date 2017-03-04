<?php

Plugin::triggerEvent('system_error.start');

// Verify if user is logged in
$this->authService->enforceTimeout();
$this->view->vars->loggedInUser = $this->authService->getAuthUser();

Plugin::triggerEvent('system_error.end');