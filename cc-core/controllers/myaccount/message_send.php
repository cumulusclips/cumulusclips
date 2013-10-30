<?php

// Init view
View::InitView ('message_send');
Plugin::triggerEvent('message_send.start');

// Verify if user is logged in
$userService = new UserService();
View::$vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf(View::$vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
View::$vars->to = null;
View::$vars->subject = null;
View::$vars->msg = null;
View::$vars->errors = array();
View::$vars->message = null;
$message = array();
$userMapper = new UserMapper();
$messageMapper = new MessageMapper();
$message = new Message();

// Verify if request came from outside page
if (!empty($_GET['username'])) {
    
    $recipient = $userMapper->getUserByUsername($_GET['username']);
    if ($recipient) {
        View::$vars->to = $recipient->username;
    }
    
// Verify if request came from reply
} elseif (!empty($_GET['msg'])) {
    $data = array('message_id' => $_GET['msg'], 'recipient' => View::$vars->loggedInUser->userId);
    $originalMessage = $messageMapper->getMessageByCustom($data);
    if ($originalMessage) {
        View::$vars->to = $originalMessage->username;
        View::$vars->subject = "Re: $originalMessage->subject";
        View::$vars->msg = "\n\n\n> " . View::$vars->to . " wrote: \n\n $originalMessage->message";
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
            if ($recipient->userId != View::$vars->loggedInUser->userId) {
                View::$vars->to = $recipient->username;
                $message->recipient = $recipient->userId;
            } else {
                View::$vars->errors['recipient'] = Language::GetText('error_recipient_self');
            }    
        } else {
            View::$vars->errors['recipient'] = Language::GetText('error_recipient_exist');
        }
    } else {
        View::$vars->errors['recipient'] = Language::GetText('error_recipient');
    }

    // Validate subject field
    if (!empty($_POST['subject'])) {
        $message->subject = trim($_POST['subject']);
        View::$vars->subject = $message['subject'];
    } else {
        View::$vars->errors['subject'] = Language::GetText('error_subject');
    }

    // Validate message field
    if (!empty($_POST['message'])) {
        $message->message = trim($_POST['message']);
        View::$vars->msg = $message['message'];
    } else {
        View::$vars->errors['message'] = Language::GetText('error_message');
    }

    // Create message if no errors were found
    if (empty(View::$vars->errors)) {
        Plugin::triggerEvent('message_send.before_send_message');
        $message->userId = View::$vars->loggedInUser->userId;
        $messageMapper->save($message);
        View::$vars->to = null;
        View::$vars->subject = null;
        View::$vars->msg = null;

        // Send recipient email notification if opted-in
        $privacyService = new PrivacyService();
        if ($privacyService->optCheck($recipient, 'newMessage')) {
            $replacements = array (
                'host'      => HOST,
                'sitename'  => $config->sitename,
                'sender'    => View::$vars->loggedInUser->username,
                'email'     => $recipient->email
            );
            $mail = new Mail();
            $mail->LoadTemplate('new_message', $replacements);
            $mail->Send($recipient->email);
        }
        View::$vars->message = Language::GetText('success_message_sent');
        View::$vars->message_type = 'success';
        Plugin::triggerEvent('message_send.send_message');
    } else {
        View::$vars->message = Language::GetText('errors_below');
        View::$vars->message .= '<br /><br /> - ' . implode('<br /> - ', View::$vars->errors);
        View::$vars->message_type = 'errors';
    }
}

// Output page
Plugin::triggerEvent('message_send.before_render');
View::Render ('myaccount/message_send.tpl');