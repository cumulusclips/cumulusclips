<?php

Plugin::triggerEvent('account.start');

// Verify if user is logged in
$this->authService->enforceAuth();
$this->authService->enforceTimeout(true);
$this->view->vars->loggedInUser = $this->authService->getAuthUser();

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
