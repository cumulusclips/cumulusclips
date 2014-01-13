<?php

// Init view
$view->InitView ('message_send');
Plugin::triggerEvent('message_send.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf($view->vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$view->vars->to = null;
$view->vars->subject = null;
$view->vars->msg = null;
$view->vars->errors = array();
$view->vars->message = null;
$message = array();
$userMapper = new UserMapper();
$messageMapper = new MessageMapper();
$message = new Message();

// Verify if request came from outside page
if (!empty($_GET['username'])) {
    
    $recipient = $userMapper->getUserByUsername($_GET['username']);
    if ($recipient) {
        $view->vars->to = $recipient->username;
    }
    
// Verify if request came from reply
} elseif (!empty($_GET['msg'])) {
    $data = array('message_id' => $_GET['msg'], 'recipient' => $view->vars->loggedInUser->userId);
    $originalMessage = $messageMapper->getMessageByCustom($data);
    if ($originalMessage) {
        $view->vars->to = $originalMessage->username;
        $view->vars->subject = "Re: $originalMessage->subject";
        $view->vars->msg = "\n\n\n> " . $view->vars->to . " wrote: \n\n $originalMessage->message";
        Plugin::triggerEvent('message_send.load_original_message');
    }
}

// Send reply if requested
if (isset($_POST['submitted'])) {

    // Validate 'to' field
    if (!empty($_POST['to'])) {

        // Verify recipient exists and isn't sending user
        $recipient = $userMapper->getUserByUsername($_POST['to']);
        if ($recipient) {
            if ($recipient->userId != $view->vars->loggedInUser->userId) {
                $view->vars->to = $recipient->username;
                $message->recipient = $recipient->userId;
            } else {
                $view->vars->errors['recipient'] = Language::GetText('error_recipient_self');
            }    
        } else {
            $view->vars->errors['recipient'] = Language::GetText('error_recipient_exist');
        }
    } else {
        $view->vars->errors['recipient'] = Language::GetText('error_recipient');
    }

    // Validate subject field
    if (!empty($_POST['subject'])) {
        $message->subject = trim($_POST['subject']);
        $view->vars->subject = $message->subject;
    } else {
        $view->vars->errors['subject'] = Language::GetText('error_subject');
    }

    // Validate message field
    if (!empty($_POST['message'])) {
        $message->message = trim($_POST['message']);
        $view->vars->msg = $message->message;
    } else {
        $view->vars->errors['message'] = Language::GetText('error_message');
    }

    // Create message if no errors were found
    if (empty($view->vars->errors)) {
        Plugin::triggerEvent('message_send.before_send_message');
        $message->userId = $view->vars->loggedInUser->userId;
        $messageMapper->save($message);
        $view->vars->to = null;
        $view->vars->subject = null;
        $view->vars->msg = null;

        // Send recipient email notification if opted-in
        $privacyService = new PrivacyService();
        if ($privacyService->optCheck($recipient, 'newMessage')) {
            $replacements = array (
                'host'      => HOST,
                'sitename'  => $config->sitename,
                'sender'    => $view->vars->loggedInUser->username,
                'email'     => $recipient->email
            );
            $mail = new Mail();
            $mail->LoadTemplate('new_message', $replacements);
            $mail->Send($recipient->email);
        }
        $view->vars->message = Language::GetText('success_message_sent');
        $view->vars->message_type = 'success';
        Plugin::triggerEvent('message_send.send_message');
    } else {
        $view->vars->message = Language::GetText('errors_below');
        $view->vars->message .= '<br /><br /> - ' . implode('<br /> - ', $view->vars->errors);
        $view->vars->message_type = 'errors';
    }
}

// Output page
Plugin::triggerEvent('message_send.before_render');
$view->Render ('myaccount/message_send.tpl');