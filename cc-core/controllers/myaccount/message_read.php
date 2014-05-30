<?php

Plugin::triggerEvent('message_read.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf($view->vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$messageMapper = new MessageMapper();

// Verify a message was chosen
if (!empty($_GET['msg']) && is_numeric($_GET['msg'])) {
    
    // Retrieve and update message
    $message = $messageMapper->getMessageByCustom(array(
        'recipient' => $view->vars->loggedInUser->userId,
        'message_id' => $_GET['msg']
    ));
    if ($message) {
        $message->status = 'read';
        $messageMapper->save($message);
        $view->vars->message = $message;
    } else {
        App::Throw404();
    }
} else {
    App::Throw404();
}

Plugin::triggerEvent('message_read.before_render');