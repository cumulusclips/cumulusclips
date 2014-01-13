<?php

// Init view
$view->InitView('myaccount');
Plugin::triggerEvent('myaccount.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf($view->vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$view->vars->new_messages = null;
$view->vars->meta->title = Functions::Replace($view->vars->meta->title, array ('username' => $view->vars->loggedInUser->username));

// Check for unread messages
$messageMapper = new MessageMapper();
$userMessages = $messageMapper->getMultipleMessagesByCustom(array(
    'recipient' => $view->vars->loggedInUser->userId,
    'status' => 'unread'
));
$view->vars->unreadMessageCount = count($userMessages);

// Output Page
Plugin::triggerEvent('myaccount.before_render');
$view->Render('myaccount/myaccount.tpl');