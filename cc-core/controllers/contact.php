<?php

// Establish page variables, objects, arrays, etc
View::InitView ('contact');
Plugin::triggerEvent('contact.start');

// Verify if user is logged in
$userService = new UserService();
View::$vars->loggedInUser = $userService->loginCheck();

View::$vars->Errors = array();
View::$vars->name = null;
View::$vars->email = null;
View::$vars->feedback = null;
View::$vars->message = null;
View::$vars->message_type = null;

/***********************
Handle form if submitted
***********************/

if (isset ($_POST['submitted'])) {
	
    // Validate name
    if (!empty ($_POST['name']) && !ctype_space ($_POST['name'])) {
        View::$vars->name = trim ($_POST['name']);
    } else {
        View::$vars->Errors['name'] = Language::GetText('error_name');
    }

    // Validate email
    $string = '/^[a-z0-9][a-z0-9_\.\-]+@[a-z0-9][a-z0-9\.-]+\.[a-z0-9]{2,4}$/i';
    if (!empty ($_POST['email']) && !ctype_space ($_POST['email']) && preg_match ($string, $_POST['email'])) {
        View::$vars->email = trim ($_POST['email']);
    } else {
        View::$vars->Errors['email'] = Language::GetText('error_email');
    }

    // Validate feedback
    if (!empty ($_POST['feedback']) && !ctype_space ($_POST['feedback'])) {
        View::$vars->feedback = trim ($_POST['feedback']);
    } else {
        View::$vars->Errors['feedback'] = Language::GetText('error_message');
    }

    // Send email if no errors
    if (empty (View::$vars->Errors)) {
        $subject = 'Message received From ' . $config->sitename;
        $Msg = "Name: " . View::$vars->name . "\n";
        $Msg .= "E-mail: " . View::$vars->email . "\n";
        $Msg .= "Message:\n" . View::$vars->feedback;
        App::Alert ($subject, $Msg);
        Plugin::triggerEvent('contact.send');

        View::$vars->message_type = 'success';
        View::$vars->message = Language::GetText('success_contact_sent');
    } else {
        View::$vars->message_type = 'errors';
        View::$vars->message = Language::GetText('errors_below');
        View::$vars->message .= '<br /><br /> - ' . implode ('<br /> - ', View::$vars->Errors);
    }
}

// Output Page
Plugin::triggerEvent('contact.before_render');
View::Render ('contact.tpl');