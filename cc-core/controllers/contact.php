<?php

### Created on March 6, 2009
### Created by Miguel A. Hurtado
### This script displays and handles the contact page


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
include (DOC_ROOT . '/includes/recaptchalib.php');
View::InitView();


// Establish page variables, objects, arrays, etc
View::$vars->logged_in = User::LoginCheck();
if (View::$vars->logged_in) View::$vars->user = new User (View::$vars->logged_in);
View::$vars->page_title = 'Techie Videos - Contact Techie Videos';
View::$vars->publickey = '6LeEaQUAAAAAACsCaDpe3cq0v0NPrnVE1Qg7v16w';
$privatekey = '6LeEaQUAAAAAAICeLh_I2dI0unaUm3JtMq6tdEZm';
$resp = NULL;
View::$vars->Errors = array();
View::$vars->name = NULL;
View::$vars->email = NULL;
View::$vars->message = NULL;
View::$vars->captcha = NULL;
View::$vars->error_msg = NULL;
View::$vars->success = NULL;





/***********************
Handle form if submitted
***********************/

if (isset ($_POST['submitted'])) {
	
    // Validate name
    if (!empty ($_POST['name']) && !ctype_space ($_POST['name'])) {
        View::$vars->name = trim ($_POST['name']);
    } else {
        View::$vars->Errors['name'] = true;
    }


    // Validate email
    $string = '/^[a-z0-9][a-z0-9_\.\-]+@[a-z0-9][a-z0-9\.-]+\.[a-z0-9]{2,4}$/i';
    if (!empty ($_POST['email']) && !ctype_space ($_POST['email']) && preg_match ($string, $_POST['email'])) {
        View::$vars->email = trim ($_POST['email']);
    } else {
        View::$vars->Errors['email'] = true;
    }


    // Validate feedback
    if (!empty ($_POST['message']) && !ctype_space ($_POST['message'])) {
        View::$vars->message = trim ($_POST['message']);
    } else {
        View::$vars->Errors['message'] = true;
    }


    // Validate word verification
    if (!empty ($_POST["recaptcha_response_field"]) && !ctype_space ($_POST["recaptcha_response_field"])) {
        $resp = recaptcha_check_answer ($privatekey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
        $captcha = ($resp->is_valid) ? true : false;
    } else {
        View::$vars->Errors['captcha'] = true;
    }


    // Send email if no errors
    if (empty (View::$vars->Errors)) {

        $to = MAIN_EMAIL;
        $subject = 'Message received From TechieVideos.com';
        $headers = 'From: Admin - TechieVideos.com <admin@techievideos.com>';
        $Msg = "Name: $name\n";
        $Msg .= "E-mail: $email\n\n";
        $Msg .= "Message:\n$message";

        @mail ($to, $subject, $Msg, $headers);
        View::$vars->success = 'Thank you! Your message has been received.';

    } else {
        View::$vars->error_msg = 'Errors were found. Please try again.';
    }
	
}


// Output Page
View::Render ('contact.tpl');

?>