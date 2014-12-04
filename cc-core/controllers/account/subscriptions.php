<?php

Plugin::triggerEvent('subscriptions.start');

// Verify if user is logged in
$userService = new UserService();
$this->view->vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf($this->view->vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$userMapper = new UserMapper();
$records_per_page = 9;
$url = HOST . '/account/subscriptions';
$this->view->vars->message = null;
$db = Registry::get('db');

// Unsubscribe user if requested
if (isset ($_GET['id']) && is_numeric ($_GET['id'])) {
    $subscriptionService = new SubscriptionService();
    if ($subscriptionService->checkSubscription($this->view->vars->loggedInUser->userId, $_GET['id'])) {
        $subscribedUser = $userMapper->getUserById($_GET['id']);
        $subscriptionService->unsubscribe($this->view->vars->loggedInUser->userId, $subscribedUser->userId);
        $this->view->vars->message = Language::GetText('success_unsubscribed', array('username' => $subscribedUser->username));
        $this->view->vars->message_type = 'success';
    }
}

// Retrieve total count
$query = "SELECT member FROM " . DB_PREFIX . "subscriptions WHERE user_id = " . $this->view->vars->loggedInUser->userId;
$db->fetchAll($query);
$total = $db->rowCount();

// Initialize pagination
$this->view->vars->pagination = new Pagination($url, $total, $records_per_page);
$start_record = $this->view->vars->pagination->getStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$subscriptionResults = $db->fetchAll($query);
$this->view->vars->subscriptions = $userMapper->getUsersFromList(
    Functions::arrayColumn($subscriptionResults, 'member')
);

Plugin::triggerEvent('subscriptions.end');