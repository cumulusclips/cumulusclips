<?php

// Establish page variables, objects, arrays, etc
$view->InitView ('register');
Plugin::Trigger ('register.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();

$resp = NULL;
$pass1 = NULL;
$pass2 = NULL;
$view->vars->message = null;
$view->vars->data = array ();
$view->vars->errors = array();




/***********************
Handle form if submitted
***********************/

if (isset ($_POST['submitted'])) {

    // Validate Username
    if (!empty ($_POST['username']) && !ctype_space ($_POST['username'])) {
        if (!User::Exist (array ('username' => $_POST['username']))) {
            $view->vars->data['username'] = htmlspecialchars (trim ($_POST['username']));
        } else {
            $view->vars->errors['username'] = Language::GetText('error_username_unavailable');
        }
    } else {
        $view->vars->errors['username'] = Language::GetText('error_username');
    }


    // Validate password
    if (!empty ($_POST['password']) && !ctype_space ($_POST['password'])) {
        $password_first = trim ($_POST['password']);
    } else {
        $view->vars->errors['password'] = Language::GetText('error_password');
    }


    // Validate password confirm
    if (!empty ($_POST['password_confirm']) && !ctype_space ($_POST['password'])) {

        if (isset ($password_first) && $password_first == $_POST['password_confirm']) {
            $view->vars->data['password'] = trim ($_POST['password']);
        } else {
            $view->vars->errors['match'] = Language::GetText('error_password_match');
        }

    } else {
        $view->vars->errors['password_confirm'] = Language::GetText('error_password_confirm');
    }


    // Validate email
    if (!empty ($_POST['email']) && preg_match ('/^[a-z0-9][a-z0-9\._-]+@[a-z0-9][a-z0-9\.-]+\.[a-z0-9]{2,4}$/i', $_POST['email'])) {
        if (!User::Exist (array ('email' => $_POST['email']))) {
            $view->vars->data['email'] = htmlspecialchars (trim ($_POST['email']));
        } else {
            $view->vars->errors['email'] = Language::GetText('error_email_unavailable');
        }
    } else {
        $view->vars->errors['email'] = Language::GetText('error_email');
    }



    ### Create user if no errors were found
    if (empty ($view->vars->errors)) {

        $view->vars->data['confirm_code'] = User::CreateToken();
        $view->vars->data['status'] = 'new';
        $view->vars->data['password'] = md5 ($view->vars->data['password']);

        Plugin::Trigger ('register.before_create');
        User::Create ($view->vars->data);
        $view->vars->message = Language::GetText('success_registered');
        $view->vars->message_type = 'success';

        $replacements = array (
            'confirm_code' => $view->vars->data['confirm_code'],
            'host' => HOST,
            'sitename' => $config->sitename
        );
        $mail = new Mail();
        $mail->LoadTemplate ('welcome', $replacements);
        $mail->Send ($view->vars->data['email']);
        Plugin::Trigger ('register.create');
        unset ($view->vars->data);

    } else {
        $view->vars->message = Language::GetText('errors_below');
        $view->vars->message .= '<br /><br /> - ' . implode ('<br /> - ', $view->vars->errors);
        $view->vars->message_type = 'errors';
    }

}


// Output Page
Plugin::Trigger ('register.before_render');
$view->Render ('register.tpl');