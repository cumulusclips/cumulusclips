<?php

Plugin::triggerEvent('mobile_languages.start');
Functions::redirectIf((boolean) Settings::get('mobile_site'), HOST . '/');

// Verify if user is logged in
$this->view->vars->loggedInUser = $this->isAuth();

Plugin::triggerEvent('mobile_languages.end');