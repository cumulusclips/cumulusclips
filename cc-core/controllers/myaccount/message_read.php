<?php

// Init view
View::InitView ('message_read');
Plugin::triggerEvent('message_read.start');

// Verify if user is logged in
$userService = new UserService();
View::$vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf(View::$vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$messageMapper = new MessageMapper();

// Verify a message was chosen
if (!empty($_GET['msg']) && is_numeric($_GET['msg'])) {
    
    // Retrieve and update message
    $message = $messageMapper->getMessageByCustom(array(
        'recipient' => View::$vars->loggedInUser->userId,
        'message_id' => $_GET['msg']
    ));
    if ($message) {
        $message->status = 'read';
        $messageMapper->save($message);
        View::$vars->message = $message;
    } else {
        App::Throw404();
    }
} else {
    App::Throw404();
}

// Outuput page
Plugin::triggerEvent('message_read.before_render');
View::Render ('myaccount/message_read.tpl');