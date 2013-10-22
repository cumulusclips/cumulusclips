<?php

// Init view
View::InitView ('change_password');
Plugin::triggerEvent('change_password.start');

// Verify if user is logged in
$userService = new UserService();
View::$vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf(View::$vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
View::$vars->errors = array();
View::$vars->message = null;
$password = null;
$confirm_password = null;

// Update password if requested
if ((isset($_POST['submitted']))) {

    // Validate Password
    if (!empty($_POST['password'])) {
        $password = $_POST['password'];
    } else {
        View::$vars->errors['password'] = Language::GetText('error_password');
    }

    // Validate Confirm Password
    if (!empty($_POST['confirm_password'])) {
        $confirm_password = $_POST['confirm_password'];
    } else {
        View::$vars->errors['confirm_password'] = Language::GetText('error_password_confirm');
    }

    // Validate passwords match
    if ($confirm_password && $password) {

        // Update password if no errors were found
        if ($confirm_password == $password) {
            View::$vars->loggedInUser->password = md5($password);
            $userMapper = new UserMapper();
            $userMapper->save(View::$vars->loggedInUser);
            View::$vars->message = Language::GetText('success_password_updated');
            View::$vars->message_type = 'success';
            Plugin::triggerEvent('change_password.change_password');
        } else {
            View::$vars->errors['match'] = TRUE;
            View::$vars->message = Language::GetText('error_password_match');
            View::$vars->message_type = 'error';
        }

    } else {
        View::$vars->message = Language::GetText('errors_below');
        View::$vars->message .= '<br /><br /> - ' . implode ('<br /> - ', View::$vars->errors);
        View::$vars->message_type = 'errors';
    }
}

// Output page
Plugin::triggerEvent('change_password.before_render');
View::Render('myaccount/change_password.tpl');