<?php

Plugin::triggerEvent('change_password.start');

// Verify if user is logged in
$userService = new UserService();
$this->view->vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf($this->view->vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$this->view->vars->errors = array();
$this->view->vars->message = null;
$password = null;
$confirm_password = null;

// Update password if requested
if ((isset($_POST['submitted']))) {

    // Validate Password
    if (!empty($_POST['password'])) {
        $password = $_POST['password'];
    } else {
        $this->view->vars->errors['password'] = Language::GetText('error_password');
    }

    // Validate Confirm Password
    if (!empty($_POST['confirm_password'])) {
        $confirm_password = $_POST['confirm_password'];
    } else {
        $this->view->vars->errors['confirm_password'] = Language::GetText('error_password_confirm');
    }

    // Validate passwords match
    if ($confirm_password && $password) {

        // Update password if no errors were found
        if ($confirm_password == $password) {
            $this->view->vars->loggedInUser->password = md5($password);
            $userMapper = new UserMapper();
            $userMapper->save($this->view->vars->loggedInUser);
            $this->view->vars->message = Language::GetText('success_password_updated');
            $this->view->vars->message_type = 'success';
        } else {
            $this->view->vars->errors['match'] = TRUE;
            $this->view->vars->message = Language::GetText('error_password_match');
            $this->view->vars->message_type = 'errors';
        }

    } else {
        $this->view->vars->message = Language::GetText('errors_below');
        $this->view->vars->message .= '<br /><br /> - ' . implode ('<br /> - ', $this->view->vars->errors);
        $this->view->vars->message_type = 'errors';
    }
}

Plugin::triggerEvent('change_password.end');