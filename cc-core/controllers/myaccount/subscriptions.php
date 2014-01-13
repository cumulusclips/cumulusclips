<?php

// Init view
$view->InitView('subscriptions');
Plugin::triggerEvent('subscriptions.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf($view->vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$userMapper = new UserMapper();
$records_per_page = 9;
$url = HOST . '/myaccount/subscriptions';
$view->vars->message = null;

// Unsubscribe user if requested
if (isset ($_GET['id']) && is_numeric ($_GET['id'])) {
    $subscriptionService = new SubscriptionService();
    if ($subscriptionService->checkSubscription($view->vars->loggedInUser->userId, $_GET['id'])) {
        $subscribedUser = $userMapper->getUserById($_GET['id']);
        $subscriptionService->unsubscribe($view->vars->loggedInUser->userId, $subscribedUser->userId);
        $view->vars->message = Language::GetText('success_unsubscribed', array('username' => $subscribedUser->username));
        $view->vars->message_type = 'success';
        Plugin::triggerEvent('subscriptions.unsubscribe');
    }
}

// Retrieve total count
$query = "SELECT member FROM " . DB_PREFIX . "subscriptions WHERE user_id = " . $view->vars->loggedInUser->userId;
$db->fetchAll($query);
$total = $db->rowCount();

// Initialize pagination
$view->vars->pagination = new Pagination($url, $total, $records_per_page);
$start_record = $view->vars->pagination->getStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$subscriptionResults = $db->fetchAll($query);
$view->vars->subscriptions = $userMapper->getUsersFromList(
    Functions::flattenArray($subscriptionResults, 'member')
);

// Output page
Plugin::triggerEvent('subscriptions.before_render');
$view->Render('myaccount/subscriptions.tpl');