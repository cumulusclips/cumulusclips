<?php

// Init view
$view->InitView('update_profile');
Plugin::triggerEvent('update_profile.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf($view->vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$userMapper = new UserMapper();
$view->vars->Errors = array();
$view->vars->message = null;
$view->vars->timestamp = time();
$_SESSION['upload_key'] = md5(md5($view->vars->timestamp) . SECRET_KEY);

// Update profile if requested
if (isset($_POST['submitted'])) {

    // Validate First Name
    if (!empty($view->vars->loggedInUser->firstName) && $_POST['first_name'] == '') {
        $view->vars->loggedInUser->firstName = '';
    } elseif (!empty($_POST['first_name'])) {
        $view->vars->loggedInUser->firstName = trim($_POST['first_name']);
    }

    // Validate Last Name
    if (!empty($view->vars->loggedInUser->lastName) && $_POST['last_name'] == '') {
        $view->vars->loggedInUser->lastName = '';
    } elseif (!empty($_POST['last_name'])) {
        $view->vars->loggedInUser->lastName = trim($_POST['last_name']);
    }

    // Validate Email
    if (!empty($_POST['email']) && preg_match('/^[a-z0-9][a-z0-9_\.\-]+@[a-z0-9][a-z0-9\.\-]+\.[a-z0-9]{2,4}$/i', $_POST['email'])) {
        $userCheck = $userMapper->getUserByCustom(array('email' => $_POST['email']));
        if (!$userCheck || $userCheck->email == $view->vars->loggedInUser->email) {
            $view->vars->loggedInUser->email = $_POST['email'];
        } else {
            $view->vars->Errors['email'] = Language::GetText('error_email_unavailable');
        }
    } else {
        $view->vars->Errors['email'] = Language::GetText('error_email');
    }

    // Validate Website
    if (!empty($_POST['website'])) {
        $website = $_POST['website'];
        if (preg_match ('/^(https?:\/\/)?[a-z0-9][a-z0-9\.-]+\.[a-z0-9]{2,4}.*$/i', $website, $matches)) {
            $website = (empty($matches[1]) ? 'http://' : '') . $website;
            $view->vars->loggedInUser->website = trim($website);
        } else {
            $view->vars->errors['website'] = Language::GetText('error_website_invalid');
        }
    } else {
        $view->vars->loggedInUser->website = '';
    }

    // Validate About Me
    if (!empty($view->vars->loggedInUser->aboutMe) && $_POST['about_me'] == '') {
        $view->vars->loggedInUser->aboutMe = '';
    } elseif (!empty($_POST['about_me'])) {
        $view->vars->loggedInUser->aboutMe = trim($_POST['about_me']);
    }

    // Update User if no errors were found
    if (empty ($view->vars->Errors)) {
        $view->vars->message = Language::GetText('success_profile_updated');
        $view->vars->message_type = 'success';
        $userMapper->save($view->vars->loggedInUser);
        Plugin::triggerEvent('update_profile.update_profile');
    } else {
        $view->vars->message = Language::GetText('errors_below');
        $view->vars->message .= '<br /><br /> - ' . implode ('<br /> - ', $view->vars->Errors);
        $view->vars->message_type = 'errors';
    }
}

// Reset avatar if requested
if (!empty($_GET['action']) && $_GET['action'] == 'reset' && !empty($view->vars->loggedInUser->avatar)) {
    try {
        Filesystem::delete(UPLOAD_PATH . '/avatars/' . $view->vars->loggedInUser->avatar);
    } catch (Exception $exception) {
        App::Alert('Error during Avatar Reset', $exception->getMessage());
    }
    $view->vars->loggedInUser->avatar = null;
    $userMapper->save($view->vars->loggedInUser);
    $view->vars->message = Language::GetText('success_avatar_reset');
    $view->vars->message_type = 'success';
    Plugin::triggerEvent('update_profile.avatar_reset');
}

// Output page
Plugin::triggerEvent('update_profile.before_render');
$view->Render('myaccount/update_profile.tpl');