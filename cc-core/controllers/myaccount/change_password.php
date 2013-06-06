<?php

// Include required files
include_once (dirname (dirname (dirname (__FILE__))) . '/config/bootstrap.php');
App::LoadClass ('User');


// Establish page variables, objects, arrays, etc
View::InitView ('change_password');
Plugin::Trigger ('change_password.start');
Functions::RedirectIf (View::$vars->logged_in = User::LoginCheck(), HOST . '/login/');
View::$vars->user = new User (View::$vars->logged_in);
View::$vars->errors = array();
View::$vars->message = null;
$password = NULL;
$confirm_password = NULL;





/***********************
Handle Form if submitted
***********************/

if ((isset ($_POST['submitted']))) {

    // Validate Password
    if (!empty ($_POST['password']) && !ctype_space ($_POST['password'])) {
        $password = $_POST['password'];
    } else {
        View::$vars->errors['password'] = Language::GetText('error_password');
    }


    // Validate Confirm Password
    if (!empty ($_POST['confirm_password']) && !ctype_space ($_POST['confirm_password'])) {
        $confirm_password = $_POST['confirm_password'];
    } else {
        View::$vars->errors['confirm_password'] = Language::GetText('error_password_confirm');
    }


    // Validate passwords match
    if ($confirm_password && $password) {

        // Update password if no errors were found
        if ($confirm_password == $password) {
            $data = array ('password' => md5 ($password));
            View::$vars->user->Update ($data);
            View::$vars->message = Language::GetText('success_password_updated');
            View::$vars->message_type = 'success';
            Plugin::Trigger ('change_password.change_password');
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
Plugin::Trigger ('change_password.before_render');
View::Render ('myaccount/change_password.tpl');

?>