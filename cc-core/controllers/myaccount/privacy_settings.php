<?php

// Init view
View::InitView ('privacy_settings');
Plugin::triggerEvent('privacy_settings.start');

// Verify if user is logged in
$userService = new UserService();
View::$vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf(View::$vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$privacyMapper = new PrivacyMapper();
View::$vars->privacy = $privacyMapper->getPrivacyByUser(View::$vars->loggedInUser->userId);
View::$vars->data = array();
View::$vars->errors = array();
View::$vars->message = null;

// Update privacy settings if requested
if (isset($_POST['submitted'])) {

    // Validate Video Comments
    if (isset($_POST['video_comment']) && in_array($_POST['video_comment'], array('0','1'))) {
        View::$vars->privacy->videoComment = (boolean) $_POST['video_comment'];
    } else {
        View::$vars->errors['video_comment'] = TRUE;
    }

    // Validate Private Message
    if (isset($_POST['new_message']) && in_array($_POST['new_message'], array('0','1'))) {
        View::$vars->privacy->newMessage = (boolean) $_POST['new_message'];
    } else {
        View::$vars->errors['new_message'] = TRUE;
    }

    // Validate New member Videos
    if (isset($_POST['new_video']) && in_array($_POST['new_video'], array('0','1'))) {
        View::$vars->privacy->newVideo = (boolean) $_POST['new_video'];
    } else {
        View::$vars->errors['new_video'] = TRUE;
    }

    // Validate Video Ready
    if (isset($_POST['video_ready']) && in_array($_POST['video_ready'], array('0','1'))) {
        View::$vars->privacy->videoReady = (boolean) $_POST['video_ready'];
    } else {
        View::$vars->errors['video_ready'] = TRUE;
    }

    if (empty(View::$vars->errors)) {
        $privacyMapper->save(View::$vars->privacy);
        View::$vars->message = Language::GetText('success_privacy_updated');
        View::$vars->message_type = 'success';
        Plugin::triggerEvent('privacy_settings.update_privacy');
    } else {
        View::$vars->message = Language::GetText('error_general');
        View::$vars->message_type = 'errors';
    }
}

// Output page
Plugin::triggerEvent('privacy_settings.before_render');
View::Render ('myaccount/privacy_settings.tpl');