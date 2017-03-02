<?php

Plugin::triggerEvent('system_error.start');

// Verify if user is logged in
$this->view->vars->loggedInUser = $this->isAuth();

Plugin::triggerEvent('system_error.end');