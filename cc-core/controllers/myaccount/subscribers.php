<?php

Plugin::triggerEvent('subscribers.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf($view->vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$records_per_page = 9;
$url = HOST . '/myaccount/subscribers';

// Retrieve total count
$query = "SELECT user_id FROM " . DB_PREFIX . "subscriptions WHERE member = " . $view->vars->loggedInUser->userId;
$db->fetchAll($query);
$total = $db->rowCount();

// Initialize pagination
$view->vars->pagination = new Pagination($url, $total, $records_per_page);
$start_record = $view->vars->pagination->getStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$subscriberResults = $db->fetchAll($query);
$userMapper = new UserMapper();
$view->vars->subscribers = $userMapper->getUsersFromList(
    Functions::arrayColumn($subscriberResults, 'user_id')
);

Plugin::triggerEvent('subscribers.before_render');