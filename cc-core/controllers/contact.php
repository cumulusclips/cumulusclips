<?php

Plugin::triggerEvent('contact.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();

// Establish page variables, objects, arrays, etc
$view->vars->Errors = array();
$view->vars->name = null;
$view->vars->email = null;
$view->vars->feedback = null;
$view->vars->message = null;
$view->vars->message_type = null;

/***********************
Handle form if submitted
***********************/

if (isset ($_POST['submitted'])) {
	
    // Validate name
    if (!empty ($_POST['name']) && !ctype_space ($_POST['name'])) {
        $view->vars->name = trim ($_POST['name']);
    } else {
        $view->vars->Errors['name'] = Language::GetText('error_name');
    }

    // Validate email
    $string = '/^[a-z0-9][a-z0-9_\.\-]+@[a-z0-9][a-z0-9\.-]+\.[a-z0-9]{2,4}$/i';
    if (!empty ($_POST['email']) && !ctype_space ($_POST['email']) && preg_match ($string, $_POST['email'])) {
        $view->vars->email = trim ($_POST['email']);
    } else {
        $view->vars->Errors['email'] = Language::GetText('error_email');
    }

    // Validate feedback
    if (!empty ($_POST['feedback']) && !ctype_space ($_POST['feedback'])) {
        $view->vars->feedback = trim ($_POST['feedback']);
    } else {
        $view->vars->Errors['feedback'] = Language::GetText('error_message');
    }

    // Send email if no errors
    if (empty ($view->vars->Errors)) {
        $subject = 'Message received From ' . $config->sitename;
        $Msg = "Name: " . $view->vars->name . "\n";
        $Msg .= "E-mail: " . $view->vars->email . "\n";
        $Msg .= "Message:\n" . $view->vars->feedback;
        App::Alert ($subject, $Msg);
        Plugin::triggerEvent('contact.send');

        $view->vars->message_type = 'success';
        $view->vars->message = Language::GetText('success_contact_sent');
    } else {
        $view->vars->message_type = 'errors';
        $view->vars->message = Language::GetText('errors_below');
        $view->vars->message .= '<br /><br /> - ' . implode ('<br /> - ', $view->vars->Errors);
    }
}

Plugin::triggerEvent('contact.before_render');