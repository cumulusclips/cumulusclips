<?php

Plugin::triggerEvent('message_send.start');

// Verify if user is logged in
$this->authService->enforceAuth();
$this->authService->enforceTimeout(true);
$this->view->vars->loggedInUser = $this->authService->getAuthUser();

// Establish page variables, objects, arrays, etc
$this->view->vars->to = null;
$this->view->vars->subject = null;
$this->view->vars->msg = null;
$this->view->vars->errors = array();
$this->view->vars->message = null;
$message = array();
$userMapper = new UserMapper();
$messageMapper = new MessageMapper();
$message = new Message();
$config = Registry::get('config');

// Verify if request came from outside page
if (!empty($_GET['username'])) {

    $recipient = $userMapper->getUserByUsername($_GET['username']);
    if ($recipient) {
        $this->view->vars->to = $recipient->username;
    }

// Verify if request came from reply
} elseif (!empty($_GET['msg'])) {
    $data = array('message_id' => $_GET['msg'], 'recipient' => $this->view->vars->loggedInUser->userId);
    $originalMessage = $messageMapper->getMessageByCustom($data);
    if ($originalMessage) {
        $this->view->vars->to = $originalMessage->username;
        $this->view->vars->subject = "Re: $originalMessage->subject";
        $this->view->vars->msg = "\n\n\n> " . $this->view->vars->to . " wrote: \n\n $originalMessage->message";
    }
}

// Send reply if requested
if (isset($_POST['submitted'])) {

    // Validate form nonce token and submission speed
    if (
        !empty($_POST['nonce'])
        && !empty($_SESSION['formNonce'])
        && !empty($_SESSION['formTime'])
        && $_POST['nonce'] == $_SESSION['formNonce']
        && time() - $_SESSION['formTime'] >= 2
    ) {

        // Validate 'to' field
        if (!empty($_POST['to'])) {

            // Verify recipient exists and isn't sending user
            $recipient = $userMapper->getUserByUsername($_POST['to']);
            if ($recipient) {
                if ($recipient->userId != $this->view->vars->loggedInUser->userId) {
                    $this->view->vars->to = $recipient->username;
                    $message->recipient = $recipient->userId;
                } else {
                    $this->view->vars->errors['recipient'] = Language::getText('error_recipient_self');
                }
            } else {
                $this->view->vars->errors['recipient'] = Language::getText('error_recipient_exist');
            }
        } else {
            $this->view->vars->errors['recipient'] = Language::getText('error_recipient');
        }

        // Validate subject field
        if (!empty($_POST['subject'])) {
            $message->subject = trim($_POST['subject']);
            $this->view->vars->subject = $message->subject;
        } else {
            $this->view->vars->errors['subject'] = Language::getText('error_subject');
        }

        // Validate message field
        if (!empty($_POST['message'])) {
            $message->message = trim($_POST['message']);
            $this->view->vars->msg = $message->message;
        } else {
            $this->view->vars->errors['message'] = Language::getText('error_message');
        }

        // Create message if no errors were found
        if (empty($this->view->vars->errors)) {
            $message->userId = $this->view->vars->loggedInUser->userId;
            $messageMapper->save($message);
            $this->view->vars->to = null;
            $this->view->vars->subject = null;
            $this->view->vars->msg = null;

            // Send recipient email notification if opted-in
            $privacyService = new PrivacyService();
            if ($privacyService->optCheck($recipient, Privacy::NEW_MESSAGE)) {
                $replacements = array (
                    'host'      => HOST,
                    'sitename'  => $config->sitename,
                    'sender'    => $this->view->vars->loggedInUser->username,
                    'email'     => $recipient->email
                );
                $mailer = new Mailer($config);
                $mailer->setTemplate('new_message', $replacements);
                $mailer->send($recipient->email);
            }
            $this->view->vars->message = Language::getText('success_message_sent');
            $this->view->vars->message_type = 'success';
        } else {
            $this->view->vars->message = Language::getText('errors_below');
            $this->view->vars->message .= '<br /><br /> - ' . implode('<br /> - ', $this->view->vars->errors);
            $this->view->vars->message_type = 'errors';
        }

    } else {
        $this->view->vars->message = Language::getText('invalid_session');
        $this->view->vars->message_type = 'errors';
    }
}

// Generate new form nonce
$this->view->vars->formNonce = md5(uniqid(rand(), true));
$_SESSION['formNonce'] = $this->view->vars->formNonce;
$_SESSION['formTime'] = time();

Plugin::triggerEvent('message_send.end');
