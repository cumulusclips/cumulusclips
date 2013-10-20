<?php

// Init view
View::InitView('update_profile');
Plugin::triggerEvent('update_profile.start');

// Verify if user is logged in
$userService = new UserService();
View::$vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf(View::$vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$userMapper = new UserMapper();
View::$vars->Errors = array();
View::$vars->message = null;
View::$vars->timestamp = time();
$_SESSION['upload_key'] = md5(md5(View::$vars->timestamp) . SECRET_KEY);

// Update profile if requested
if (isset($_POST['submitted'])) {

    // Validate First Name
    if (!empty(View::$vars->user->first_name) && $_POST['first_name'] == '') {
        View::$vars->loggedInUser->firstName = '';
    } elseif (!empty($_POST['first_name'])) {
        View::$vars->loggedInUser->firstName = trim($_POST['first_name']);
    }

    // Validate Last Name
    if (!empty(View::$vars->user->last_name) && $_POST['last_name'] == '') {
        View::$vars->loggedInUser->lastName = '';
    } elseif (!empty($_POST['last_name'])) {
        View::$vars->loggedInUser->lastName = trim($_POST['last_name']);
    }

    // Validate Email
    if (!empty($_POST['email']) && preg_match('/^[a-z0-9][a-z0-9_\.\-]+@[a-z0-9][a-z0-9\.\-]+\.[a-z0-9]{2,4}$/i', $_POST['email'])) {
        if (!$userMapper->getUserByCustom(array('email' => $_POST['email']))) {
            View::$vars->loggedInUser->email = $_POST['email'];
        } else {
            View::$vars->Errors['email'] = Language::GetText('error_email_unavailable');
        }
    } else {
        View::$vars->Errors['email'] = Language::GetText('error_email');
    }

    // Validate Website
    if (!empty($_POST['website'])) {
        $website = $_POST['website'];
        if (preg_match ('/^(https?:\/\/)?[a-z0-9][a-z0-9\.-]+\.[a-z0-9]{2,4}.*$/i', $website, $matches)) {
            $website = (empty($matches[1]) ? 'http://' : '') . $website;
            View::$vars->loggedInUser->website = trim($website);
        } else {
            View::$vars->errors['website'] = Language::GetText('error_website_invalid');
        }
    } else {
        View::$vars->loggedInUser->website = '';
    }

    // Validate About Me
    if (!empty(View::$vars->user->about_me) && $_POST['about_me'] == '') {
        View::$vars->loggedInUser->aboutMe = '';
    } elseif (!empty($_POST['about_me'])) {
        View::$vars->loggedInUser->aboutMe = trim($_POST['about_me']);
    }

    // Update User if no errors were found
    if (empty (View::$vars->Errors)) {
        View::$vars->message = Language::GetText('success_profile_updated');
        View::$vars->message_type = 'success';
        $userMapper->save(View::$vars->loggedInUser);
        Plugin::triggerEvent('update_profile.update_profile');
    } else {
        View::$vars->message = Language::GetText('errors_below');
        View::$vars->message .= '<br /><br /> - ' . implode ('<br /> - ', View::$vars->Errors);
        View::$vars->message_type = 'errors';
    }
}

// Reset avatar if requested
if (!empty($_GET['action']) && $_GET['action'] == 'reset' && !empty(View::$vars->loggedInUser->avatar)) {
    try {
        Filesystem::delete(UPLOAD_PATH . '/avatars/' . View::$vars->loggedInUser->avatar);
    } catch (Exception $exception) {
        App::Alert('Error during Avatar Reset', $exception->getMessage());
    }
    View::$vars->loggedInUser->avatar = '';
    $userMapper->save(View::$vars->loggedInUser);
    View::$vars->message = Language::GetText('success_avatar_reset');
    View::$vars->message_type = 'success';
    Plugin::triggerEvent('update_profile.avatar_reset');
}

// Output page
Plugin::triggerEvent('update_profile.before_render');
View::Render('myaccount/update_profile.tpl');