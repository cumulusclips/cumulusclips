<?php

// Init view
View::InitView('subscriptions');
Plugin::triggerEvent('subscriptions.start');

// Verify if user is logged in
$userService = new UserService();
View::$vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf(View::$vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$userMapper = new UserMapper();
$records_per_page = 9;
$url = HOST . '/myaccount/subscriptions';
View::$vars->message = null;

// Unsubscribe user if requested
if (isset ($_GET['id']) && is_numeric ($_GET['id'])) {
    $subscriptionService = new SubscriptionService();
    if ($subscriptionService->checkSubscription(View::$vars->loggedInUser->userId, $_GET['id'])) {
        $subscribedUser = $userMapper->getUserById($_GET['id']);
        $subscriptionService->unsubscribe(View::$vars->loggedInUser->userId, $subscribedUser->userId);
        View::$vars->message = Language::GetText('success_unsubscribed', array('username' => $subscribedUser->username));
        View::$vars->message_type = 'success';
        Plugin::triggerEvent('subscriptions.unsubscribe');
    }
}

// Retrieve total count
$query = "SELECT member FROM " . DB_PREFIX . "subscriptions WHERE user_id = " . View::$vars->loggedInUser->userId;
$db->fetchAll($query);
$total = $db->rowCount();

// Initialize pagination
View::$vars->pagination = new Pagination($url, $total, $records_per_page);
$start_record = View::$vars->pagination->getStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$subscriptionResults = $db->fetchAll($query);
View::$vars->subscriptions = $userMapper->getMultipleUsersById(
    Functions::flattenArray($subscriptionResults, 'member')
);

// Output page
Plugin::triggerEvent('subscriptions.before_render');
View::Render('myaccount/subscriptions.tpl');