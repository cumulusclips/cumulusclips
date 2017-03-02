<?php

Plugin::triggerEvent('system_404.start');

// Verify if user is logged in
$this->view->vars->loggedInUser = $this->isAuth();

header ("HTTP/1.0 404 Not Found");
Plugin::triggerEvent('system_404.end');