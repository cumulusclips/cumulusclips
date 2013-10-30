<?php

// Init view
View::InitView ('subscribers');
Plugin::triggerEvent('subscribers.start');

// Verify if user is logged in
$userService = new UserService();
View::$vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf(View::$vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$records_per_page = 9;
$url = HOST . '/myaccount/subscribers';

// Retrieve total count
$query = "SELECT user_id FROM " . DB_PREFIX . "subscriptions WHERE member = " . View::$vars->loggedInUser->userId;
$db->fetchAll($query);
$total = $db->rowCount();

// Initialize pagination
View::$vars->pagination = new Pagination($url, $total, $records_per_page);
$start_record = View::$vars->pagination->getStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$subscriberResults = $db->fetchAll($query);
$userMapper = new UserMapper();
View::$vars->subscribers = $userMapper->getMultipleUsersById(
    Functions::flattenArray($subscriberResults, 'user_id')
);

// Output page
Plugin::triggerEvent('subscribers.before_render');
View::Render('myaccount/subscribers.tpl');