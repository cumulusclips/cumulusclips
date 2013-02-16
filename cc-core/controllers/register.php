<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Mail');


// Establish page variables, objects, arrays, etc
View::InitView ('register');
Plugin::Trigger ('register.start');
View::$vars->logged_in = User::LoginCheck();
Functions::RedirectIf (!View::$vars->logged_in, HOST . '/myaccount/');
$resp = NULL;
$pass1 = NULL;
$pass2 = NULL;
View::$vars->message = null;
View::$vars->data = array ();
View::$vars->errors = array();




/***********************
Handle form if submitted
***********************/

if (isset ($_POST['submitted'])) {

    // Validate Username
    if (!empty ($_POST['username']) && !ctype_space ($_POST['username'])) {
        if (!User::Exist (array ('username' => $_POST['username']))) {
            View::$vars->data['username'] = htmlspecialchars (trim ($_POST['username']));
        } else {
            View::$vars->errors['username'] = Language::GetText('error_username_unavailable');
        }
    } else {
        View::$vars->errors['username'] = Language::GetText('error_username');
    }


    // Validate password
    if (!empty ($_POST['password']) && !ctype_space ($_POST['password'])) {
        $password_first = trim ($_POST['password']);
    } else {
        View::$vars->errors['password'] = Language::GetText('error_password');
    }


    // Validate password confirm
    if (!empty ($_POST['password_confirm']) && !ctype_space ($_POST['password'])) {

        if (isset ($password_first) && $password_first == $_POST['password_confirm']) {
            View::$vars->data['password'] = trim ($_POST['password']);
        } else {
            View::$vars->errors['match'] = Language::GetText('error_password_match');
        }

    } else {
        View::$vars->errors['password_confirm'] = Language::GetText('error_password_confirm');
    }


    // Validate email
    if (!empty ($_POST['email']) && preg_match ('/^[a-z0-9][a-z0-9\._-]+@[a-z0-9][a-z0-9\.-]+\.[a-z0-9]{2,4}$/i', $_POST['email'])) {
        if (!User::Exist (array ('email' => $_POST['email']))) {
            View::$vars->data['email'] = htmlspecialchars (trim ($_POST['email']));
        } else {
            View::$vars->errors['email'] = Language::GetText('error_email_unavailable');
        }
    } else {
        View::$vars->errors['email'] = Language::GetText('error_email');
    }



    ### Create user if no errors were found
    if (empty (View::$vars->errors)) {

        View::$vars->data['confirm_code'] = User::CreateToken();
        View::$vars->data['status'] = 'new';
        View::$vars->data['password'] = md5 (View::$vars->data['password']);

        Plugin::Trigger ('register.before_create');
        User::Create (View::$vars->data);
        View::$vars->message = Language::GetText('success_registered');
        View::$vars->message_type = 'success';

        $replacements = array (
            'confirm_code' => View::$vars->data['confirm_code'],
            'host' => HOST,
            'sitename' => $config->sitename
        );
        $mail = new Mail();
        $mail->LoadTemplate ('welcome', $replacements);
        $mail->Send (View::$vars->data['email']);
        Plugin::Trigger ('register.create');
        unset (View::$vars->data);

    } else {
        View::$vars->message = Language::GetText('errors_below');
        View::$vars->message .= '<br /><br /> - ' . implode ('<br /> - ', View::$vars->errors);
        View::$vars->message_type = 'errors';
    }

}


// Output Page
Plugin::Trigger ('register.before_render');
View::Render ('register.tpl');

?>