<?php

Plugin::triggerEvent('privacy_settings.start');

// Verify if user is logged in
$userService = new UserService();
$this->view->vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf($this->view->vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$privacyMapper = new PrivacyMapper();
$this->view->vars->privacy = $privacyMapper->getPrivacyByUser($this->view->vars->loggedInUser->userId);
$this->view->vars->data = array();
$this->view->vars->errors = array();
$this->view->vars->message = null;

// Update privacy settings if requested
if (isset($_POST['submitted'])) {

    // Validate Video Comments
    if (isset($_POST['video_comment']) && in_array($_POST['video_comment'], array('0','1'))) {
        $this->view->vars->privacy->videoComment = (boolean) $_POST['video_comment'];
    } else {
        $this->view->vars->errors['video_comment'] = TRUE;
    }

    // Validate Private Message
    if (isset($_POST['new_message']) && in_array($_POST['new_message'], array('0','1'))) {
        $this->view->vars->privacy->newMessage = (boolean) $_POST['new_message'];
    } else {
        $this->view->vars->errors['new_message'] = TRUE;
    }

    // Validate New member Videos
    if (isset($_POST['new_video']) && in_array($_POST['new_video'], array('0','1'))) {
        $this->view->vars->privacy->newVideo = (boolean) $_POST['new_video'];
    } else {
        $this->view->vars->errors['new_video'] = TRUE;
    }

    // Validate Video Ready
    if (isset($_POST['video_ready']) && in_array($_POST['video_ready'], array('0','1'))) {
        $this->view->vars->privacy->videoReady = (boolean) $_POST['video_ready'];
    } else {
        $this->view->vars->errors['video_ready'] = TRUE;
    }

    // Validate Comment Reply
    if (isset($_POST['commentReply']) && in_array($_POST['commentReply'], array('0','1'))) {
        $this->view->vars->privacy->commentReply = (boolean) $_POST['commentReply'];
    } else {
        $this->view->vars->errors['commentReply'] = TRUE;
    }

    if (empty($this->view->vars->errors)) {
        $privacyMapper->save($this->view->vars->privacy);
        $this->view->vars->message = Language::GetText('success_privacy_updated');
        $this->view->vars->message_type = 'success';
    } else {
        $this->view->vars->message = Language::GetText('error_general');
        $this->view->vars->message_type = 'errors';
    }
}

Plugin::triggerEvent('privacy_settings.end');