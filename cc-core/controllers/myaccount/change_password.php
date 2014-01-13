<?php

// Init view
$view->InitView ('change_password');
Plugin::triggerEvent('change_password.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf($view->vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$view->vars->errors = array();
$view->vars->message = null;
$password = null;
$confirm_password = null;

// Update password if requested
if ((isset($_POST['submitted']))) {

    // Validate Password
    if (!empty($_POST['password'])) {
        $password = $_POST['password'];
    } else {
        $view->vars->errors['password'] = Language::GetText('error_password');
    }

    // Validate Confirm Password
    if (!empty($_POST['confirm_password'])) {
        $confirm_password = $_POST['confirm_password'];
    } else {
        $view->vars->errors['confirm_password'] = Language::GetText('error_password_confirm');
    }

    // Validate passwords match
    if ($confirm_password && $password) {

        // Update password if no errors were found
        if ($confirm_password == $password) {
            $view->vars->loggedInUser->password = md5($password);
            $userMapper = new UserMapper();
            $userMapper->save($view->vars->loggedInUser);
            $view->vars->message = Language::GetText('success_password_updated');
            $view->vars->message_type = 'success';
            Plugin::triggerEvent('change_password.change_password');
        } else {
            $view->vars->errors['match'] = TRUE;
            $view->vars->message = Language::GetText('error_password_match');
            $view->vars->message_type = 'error';
        }

    } else {
        $view->vars->message = Language::GetText('errors_below');
        $view->vars->message .= '<br /><br /> - ' . implode ('<br /> - ', $view->vars->errors);
        $view->vars->message_type = 'errors';
    }
}

// Output page
Plugin::triggerEvent('change_password.before_render');
$view->Render('myaccount/change_password.tpl');