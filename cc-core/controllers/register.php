<?php

### Created on March 9, 2009
### Created by Miguel A. Hurtado
### This script allows users to register


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Privacy');
App::LoadClass ('EmailTemplate');
include (DOC_ROOT . '/includes/recaptchalib.php');
include (DOC_ROOT . '/includes/username_reserve.php');
View::InitView();


// Establish page variables, objects, arrays, etc
View::$vars->logged_in = User::LoginCheck() ? header (HOST . '/myaccount/') : '';
View::$vars->page_title = 'Techie Videos - Create an account';
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
        View::$vars->Errors['terms'] = 'You must agree to our Terms of Use';
    }


    // Validate Username
    if (!empty ($_POST['username']) && !ctype_space ($_POST['username'])) {
        if (!User::Exist (array ('username' => $_POST['username'])) && !in_array ($_POST['username'], $user_reserve)) {
            $data['username'] = htmlspecialchars (trim ($_POST['username']));
        } else {
            View::$vars->Errors['username'] = 'That username is unavailable';
        }
    } else {
        View::$vars->Errors['username'] = 'You must enter a valid username';
    }


    // Validate password
    if (!empty ($_POST['password']) && !ctype_space ($_POST['password'])) {
        $pass1 = htmlspecialchars (trim ($_POST['password']));
    } else {
        View::$vars->Errors['password'] = 'You must enter a valid password';
    }


    // Validate confirm password
    if (!empty ($_POST['confirm']) && !ctype_space ($_POST['confirm'])) {
        $pass2 = htmlspecialchars (trim ($_POST['confirm']));
        if ($pass1 == $pass2) {
            View::$vars->data['password'] = $pass1;
        } else {
            View::$vars->Errors['match'] = 'Your passwords don\'t match';
        }
    } else {
        View::$vars->Errors['confirm'] = 'You must confirm your password';
    }


    // Validate Captcha
    if (!empty ($_POST["recaptcha_response_field"]) && !ctype_space ($_POST["recaptcha_response_field"])) {
        $resp = recaptcha_check_answer ($privatekey, $_SERVER["REMOTE_ADDR"], $_POST['recaptcha_challenge_field'], $_POST["recaptcha_response_field"]);
        if (!$resp->is_valid) {
            View::$vars->Errors['captcha'] = 'You submitted an incorrect security text';
        }
    } else {
        View::$vars->Errors['captcha'] = 'You must enter the security text';
    }


    // Validate email
    if (!empty ($_POST['email']) && preg_match ('/^[a-z0-9][a-z0-9\._-]+@[a-z0-9][a-z0-9\.-]+\.[a-z0-9]{2,4}$/i', $_POST['email'])) {
        if (!User::Exist (array ('email' => $_POST['email']))) {
            View::$vars->data['email'] = htmlspecialchars (trim ($_POST['email']));
        } else {
            View::$vars->Errors['email-duplicate'] = 'That email address is already in use';
        }
    } else {
        View::$vars->Errors['email'] = 'You must submit a valid email address';
    }



    ### Create user if no errors were found
    if (empty (View::$vars->Errors)) {

        View::$vars->data['confirm_code'] = User::CreateToken();
        $id = User::Create (View::$vars->data);
        Privacy::Create ($id);
        View::$vars->success = 'Thank you, you have successfully created your account! ';
        View::$vars->success .= 'Please check your email to activate your account, make sure to look in both your Inbox and Junk folders for your activation email.';

        $template = new EmailTemplate ('/new_account.htm');
        $Msg = array (
            'confirm_code' => View::$vars->data['confirm_code'],
            'host'  => HOST
        );
        $template->Replace ($Msg);
        $template->Send (View::$vars->data['email']);

    } else {

        View::$vars->error_msg = 'Errors were found. Please correct the errors below and try again.<br />';
        foreach (View::$vars->Errors as $value) {
            View::$vars->error_msg .= "\n<br /> - $value";
        }

    }

}


// Output Page
View::AddJs ('username_validation.js');
View::Render ('register.tpl');

?>