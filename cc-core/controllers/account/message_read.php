<?php

Plugin::triggerEvent('message_read.start');

// Verify if user is logged in
$this->authService->enforceAuth();
$this->authService->enforceTimeout(true);
$this->view->vars->loggedInUser = $this->authService->getAuthUser();

// Establish page variables, objects, arrays, etc
$messageMapper = new MessageMapper();

// Verify a message was chosen
if (!empty($_GET['msg']) && is_numeric($_GET['msg'])) {

    // Retrieve and update message
    $message = $messageMapper->getMessageByCustom(array(
        'recipient' => $this->view->vars->loggedInUser->userId,
        'message_id' => $_GET['msg']
    ));
    if ($message) {
        $message->status = 'read';
        $messageMapper->save($message);
        $this->view->vars->message = $message;
    } else {
        App::Throw404();
    }
} else {
    App::throw404();
}

Plugin::triggerEvent('message_read.end');
