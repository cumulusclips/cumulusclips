<?php

// Init view
View::InitView('myaccount');
Plugin::triggerEvent('myaccount.start');

// Login check
View::$vars->loggedInUser = UserService::loginCheck();
Functions::RedirectIf(View::$vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
View::$vars->new_messages = null;
View::$vars->meta->title = Functions::Replace(View::$vars->meta->title, array ('username' => View::$vars->loggedInUser->username));

// Check for unread messages
$messageMapper = new MessageMapper();
$userMessages = $messageMapper->getMultipleMessagesByCustom(array(
    'recipient' => View::$vars->loggedInUser->userId,
    'status' => 'unread'
));
View::$vars->unreadMessageCount = count($userMessages);

// Output Page
Plugin::triggerEvent('myaccount.before_render');
View::Render('myaccount/myaccount.tpl');