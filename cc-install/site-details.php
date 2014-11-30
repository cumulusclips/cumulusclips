<?php

// Send user to appropriate step
if (!isset ($settings->completed)) {
    header ("Location: " . HOST . '/cc-install/');
    exit();
} else if (!in_array ('database', $settings->completed)) {
    header ("Location: " . HOST . '/cc-install/?database');
    exit();
} else if (in_array ('site-details', $settings->completed)) {
    header ("Location: " . HOST . '/cc-install/?complete');
    exit();
}


// Establish needed vars.
$page_title = 'CumulusClips - Site Details';
$errors = array();
$error_msg = null;


// Handle form if submitted
if (isset ($_POST['submitted'])) {

    // Validate url
    $pattern = '/^https?:\/\/[a-z0-9][a-z0-9\.\-]*[a-z0-9\/\_\.\-]*$/i';
    if (!empty ($_POST['url']) && !ctype_space ($_POST['url']) && preg_match ($pattern, $_POST['url'])) {
        $url = rtrim ($_POST['url'], '/');
    } else {
        $errors['url'] = "A valid base URL is needed";
    }


    // Validate sitename
    if (!empty ($_POST['sitename']) && !ctype_space ($_POST['sitename'])) {
        $sitename = trim ($_POST['sitename']);
    } else {
        $errors['sitename'] = "A valid sitename is needed";
    }


    // Validate username
    $pattern = '/^[a-z0-9]+$/i';
    if (!empty ($_POST['username']) && !ctype_space ($_POST['username']) && preg_match ($pattern, $_POST['username'])) {
        $username = trim ($_POST['username']);
    } else {
        $errors['username'] = "A valid username is needed";
    }


    // Validate password
    if (!empty ($_POST['password']) && !ctype_space ($_POST['password'])) {
        $password = trim ($_POST['password']);
    } else {
        $errors['password'] = "A valid password is needed";
    }


    // Validate email
    $pattern = '/^[a-z0-9][a-z0-9\_\.\-]*@[a-z0-9][a-z0-9\.\-]*\.[a-z0-9]{2,4}$/i';
    if (!empty ($_POST['email']) && !ctype_space ($_POST['email']) && preg_match ($pattern, $_POST['email'])) {
        $email = trim ($_POST['email']);
    } else {
        $errors['email'] = "A valid email address is needed";
    }


    // Store information if no form errors were found
    if (empty ($errors)) {

        // Store information & redirect user
        $settings->base_url = $url;
        $settings->sitename = $sitename;
        $settings->admin_username = $username;
        $settings->admin_password = $password;
        $settings->admin_email = $email;
        $settings->completed[] = 'site-details';
        $_SESSION['settings'] = serialize ($settings);
        header ("Location: " . HOST . '/cc-install/?complete');
        exit();

    } else {
        $error_msg = '<p>Errors were found. Please correct them and try again.<br /><br /> - ';
        $error_msg .= implode ('<br / >- ', $errors);
    }

}


// Output page
include_once (INSTALL . '/views/site-details.tpl');