<?php

### Created on March 9, 2009
### Created by Miguel A. Hurtado
### This script allows users to register


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('EmailTemplate');
App::LoadClass ('Recaptcha');


// Establish page variables, objects, arrays, etc
View::InitView ('register');
Plugin::Trigger ('register.start');
View::$vars->logged_in = User::LoginCheck() ? header (HOST . '/myaccount/') : '';
View::$vars->publickey = '6LeEaQUAAAAAACsCaDpe3cq0v0NPrnVE1Qg7v16w';
$privatekey = '6LeEaQUAAAAAAICeLh_I2dI0unaUm3JtMq6tdEZm';
$resp = NULL;
$pass1 = NULL;
$pass2 = NULL;
View::$vars->success = NULL;
View::$vars->error_msg = NULL;
View::$vars->data = array ();
View::$vars->Errors = array();




/***********************
Handle form if submitted
***********************/

if (isset ($_POST['submitted'])) {


    // Validate terms
    if (!isset ($_POST['terms']) || $_POST['terms'] != 'Agree') {
        View::$vars->Errors['terms'] = Language::GetText('error_terms');
    }


    // Validate Username
    if (!empty ($_POST['username']) && !ctype_space ($_POST['username'])) {
        if (!User::Exist (array ('username' => $_POST['username']))) {
            $data['username'] = htmlspecialchars (trim ($_POST['username']));
        } else {
            View::$vars->Errors['username'] = Language::GetText('error_username_unavailable');
        }
    } else {
        View::$vars->Errors['username'] = Language::GetText('error_username');
    }


    // Validate password
    if (!empty ($_POST['password']) && !ctype_space ($_POST['password'])) {
        $pass1 = htmlspecialchars (trim ($_POST['password']));
    } else {
        View::$vars->Errors['password'] = Language::GetText('error_password');
    }


    // Validate confirm password
    if (!empty ($_POST['confirm']) && !ctype_space ($_POST['confirm'])) {
        $pass2 = htmlspecialchars (trim ($_POST['confirm']));
        if ($pass1 == $pass2) {
            View::$vars->data['password'] = $pass1;
        } else {
            View::$vars->Errors['match'] = Language::GetText('error_password_match');
        }
    } else {
        View::$vars->Errors['confirm'] = Language::GetText('error_password_confirm');
    }


    // Validate Captcha
    if (!empty ($_POST["recaptcha_response_field"]) && !ctype_space ($_POST["recaptcha_response_field"])) {
        $resp = recaptcha_check_answer ($privatekey, $_SERVER["REMOTE_ADDR"], $_POST['recaptcha_challenge_field'], $_POST["recaptcha_response_field"]);
        if (!$resp->is_valid) {
            View::$vars->Errors['captcha'] = Language::GetText('error_security_text');
        }
    } else {
        View::$vars->Errors['captcha'] = Language::GetText('error_security_text');
    }


    // Validate email
    if (!empty ($_POST['email']) && preg_match ('/^[a-z0-9][a-z0-9\._-]+@[a-z0-9][a-z0-9\.-]+\.[a-z0-9]{2,4}$/i', $_POST['email'])) {
        if (!User::Exist (array ('email' => $_POST['email']))) {
            View::$vars->data['email'] = htmlspecialchars (trim ($_POST['email']));
        } else {
            View::$vars->Errors['email'] = Language::GetText('error_email_unavailable');
        }
    } else {
        View::$vars->Errors['email'] = Language::GetText('error_email_invalid');
    }



    ### Create user if no errors were found
    if (empty (View::$vars->Errors)) {

        View::$vars->data['confirm_code'] = User::CreateToken();
        Plugin::Trigger ('register.before_create');
        User::Create (View::$vars->data);
        View::$vars->success = Language::GetText('success_registered');

        $template = new EmailTemplate ('/new_account.htm');
        $Msg = array (
            'confirm_code' => View::$vars->data['confirm_code'],
            'host'  => HOST
        );
        $template->Replace ($Msg);
        $template->Send (View::$vars->data['email']);
        Plugin::Trigger ('register.create');

    } else {
        View::$vars->error_msg = Language::GetText('errors_below');
        View::$vars->error_msg .= '<br /><br /> - ' . implode ('<br /> - ', View::$vars->Errors);
    }

}


// Output Page
Plugin::Trigger ('register.before_render');
View::Render ('register.tpl');

?>