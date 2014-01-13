<?php

// Init view
$view->InitView ('privacy_settings');
Plugin::triggerEvent('privacy_settings.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf($view->vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$privacyMapper = new PrivacyMapper();
$view->vars->privacy = $privacyMapper->getPrivacyByUser($view->vars->loggedInUser->userId);
$view->vars->data = array();
$view->vars->errors = array();
$view->vars->message = null;

// Update privacy settings if requested
if (isset($_POST['submitted'])) {

    // Validate Video Comments
    if (isset($_POST['video_comment']) && in_array($_POST['video_comment'], array('0','1'))) {
        $view->vars->privacy->videoComment = (boolean) $_POST['video_comment'];
    } else {
        $view->vars->errors['video_comment'] = TRUE;
    }

    // Validate Private Message
    if (isset($_POST['new_message']) && in_array($_POST['new_message'], array('0','1'))) {
        $view->vars->privacy->newMessage = (boolean) $_POST['new_message'];
    } else {
        $view->vars->errors['new_message'] = TRUE;
    }

    // Validate New member Videos
    if (isset($_POST['new_video']) && in_array($_POST['new_video'], array('0','1'))) {
        $view->vars->privacy->newVideo = (boolean) $_POST['new_video'];
    } else {
        $view->vars->errors['new_video'] = TRUE;
    }

    // Validate Video Ready
    if (isset($_POST['video_ready']) && in_array($_POST['video_ready'], array('0','1'))) {
        $view->vars->privacy->videoReady = (boolean) $_POST['video_ready'];
    } else {
        $view->vars->errors['video_ready'] = TRUE;
    }

    if (empty($view->vars->errors)) {
        $privacyMapper->save($view->vars->privacy);
        $view->vars->message = Language::GetText('success_privacy_updated');
        $view->vars->message_type = 'success';
        Plugin::triggerEvent('privacy_settings.update_privacy');
    } else {
        $view->vars->message = Language::GetText('error_general');
        $view->vars->message_type = 'errors';
    }
}

// Output page
Plugin::triggerEvent('privacy_settings.before_render');
$view->Render ('myaccount/privacy_settings.tpl');