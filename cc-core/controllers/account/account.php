<?php

Plugin::triggerEvent('account.start');

// Verify if user is logged in
$userService = new UserService();
$this->view->vars->loggedInUser = $userService->loginCheck();
Functions::redirectIf($this->view->vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$this->view->vars->new_messages = null;
$this->view->vars->meta->title = Functions::replace($this->view->vars->meta->title, array('username' => $this->view->vars->loggedInUser->username));

// Check for unread messages
$messageMapper = new MessageMapper();
$userMessages = $messageMapper->getMultipleMessagesByCustom(array(
    'recipient' => $this->view->vars->loggedInUser->userId,
    'status' => 'unread'
));
$this->view->vars->unreadMessageCount = count($userMessages);

Plugin::triggerEvent('account.end');