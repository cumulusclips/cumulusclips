<?php

### Created on March 24, 2009
### Created by Miguel A. Hurtado
### This script allows the user to edit their profile


// Include required files
include ('../../config/bootstrap.php');
App::LoadClass ('User');
View::InitView();


// Establish page variables, objects, arrays, etc
View::LoadPage ('update_profile');
View::$vars->logged_in = User::LoginCheck (HOST . '/login/');
View::$vars->user = new User (View::$vars->logged_in);
View::$vars->Errors = array();
View::$vars->error_msg = NULL;
View::$vars->success_msg = NULL;
$duplicate = NULL;





/**************************
 * Handle Form if submitted
 *************************/

if (isset ($_POST['submitted'])) {

    // Validate First Name
    if (!empty (View::$vars->user->first_name) && $_POST['first_name'] == '') {
        View::$vars->data['first_name'] = '';
    } elseif (!empty ($_POST['first_name']) && !ctype_space ($_POST['first_name'])) {
        View::$vars->data['first_name'] = htmlspecialchars ($_POST['first_name']);
    }


    // Validate Last Name
    if (!empty (View::$vars->user->last_name) && $_POST['last_name'] == '') {
        View::$vars->data['last_name'] = '';
    } elseif (!empty ($_POST['last_name']) && !ctype_space ($_POST['last_name'])) {
        View::$vars->data['last_name'] = htmlspecialchars ($_POST['last_name']);
    }


    // Validate Email
    if (!empty ($_POST['email']) && !ctype_space ($_POST['email']) && preg_match ('/^[a-z0-9][a-z0-9_\.\-]+@[a-z0-9][a-z0-9\.\-]+\.[a-z0-9]{2,4}$/i',$_POST['email'])) {
        $email = array ('email' => $_POST['email']);
        $id = User::Exist ($email);
        if (!$id || $id == View::$vars->user->user_id) {
            View::$vars->data['email'] = $_POST['email'];
        } else {
            View::$vars->Errors['email'] = Language::GetText('error_email_unavailable');
        }

    } else {
        View::$vars->Errors['email'] = Language::GetText('error_email');
    }



    // Validate Website
    if (!empty (View::$vars->user->website) && $_POST['website'] == '') {
        View::$vars->data['website'] = '';
    } elseif (!empty ($_POST['website']) && !ctype_space ($_POST['website'])) {
        View::$vars->data['website'] = htmlspecialchars ($_POST['website']);
    }



    // Validate About Me
    if (!empty (View::$vars->user->about_me) && $_POST['about_me'] == '') {
        View::$vars->data['about_me'] = '';
    } elseif (!empty ($_POST['about_me']) && !ctype_space ($_POST['about_me'])) {
        View::$vars->data['about_me'] = htmlspecialchars ($_POST['about_me']);
    }



    // Update User if no errors were found
    if (empty (View::$vars->Errors)) {
        View::$vars->user->Update (View::$vars->data);
        View::$vars->success_msg = Language::GetText('success_profile_updated');
    } else {
        View::$vars->error_msg = Language::GetText('errors_below');
        View::$vars->error_msg .= '<br /><br /> - ' . implode ('<br /> - ', View::$vars->Errors);
    }



}

// Output page
View::SetLayout ('portal.layout.tpl');
View::Render ('myaccount/update_profile.tpl');

?>