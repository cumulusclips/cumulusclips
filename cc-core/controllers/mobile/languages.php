<?php

Plugin::triggerEvent('mobile_languages.start');
Functions::redirectIf((boolean) Settings::get('mobile_site'), HOST . '/');

// Verify if user is logged in
$this->authService->enforceTimeout();
$this->view->vars->loggedInUser = $this->authService->getAuthUser();

Plugin::triggerEvent('mobile_languages.end');