<?php

### Created on April 5, 2009
### Created by Miguel A. Hurtado
### This script allows users to change their password


// Include required files
include ('../../config/bootstrap.php');
App::LoadClass ('User');
View::InitView();


// Establish page variables, objects, arrays, etc
View::$vars->logged_in = User::LoginCheck (HOST . '/login/');
View::$vars->page_title = 'Techie Videos - Change Account Password';
View::$vars->user = new User (View::$vars->logged_in);
View::$vars->Errors = array();
View::$vars->error_msg = NULL;
View::$vars->success = NULL;
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
        View::$vars->Errors['password'] = Language::GetText('error_password');
    }


    // Validate Confirm Password
    if (!empty ($_POST['confirm_password']) && !ctype_space ($_POST['confirm_password'])) {
        $confirm_password = $_POST['confirm_password'];
    } else {
        View::$vars->Errors['confirm_password'] = Language::GetText('error_password_confirm');
    }


    // Validate passwords match
    if ($confirm_password && $password) {

        // Update password if no errors were found
        if ($confirm_password == $password) {
            $data = array ('password' => $password);
            View::$vars->user->Update ($data);
            View::$vars->success = Language::GetText('success_password_updated');
        } else {
            View::$vars->Errors['match'] = TRUE;
            View::$vars->error_msg = Language::GetText('error_password_match');
        }

    } else {
        View::$vars->error_msg = Language::GetText('errors_below');
        View::$vars->error_msg .= '<br /><br /> - ' . implode ('<br /> - ', View::$vars->Errors);
    }

}


// Output page
View::SetLayout ('portal.layout.tpl');
View::Render ('myaccount/change_password.tpl');

?>