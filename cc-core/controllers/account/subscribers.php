<?php

Plugin::triggerEvent('subscribers.start');

// Verify if user is logged in
$userService = new UserService();
$this->view->vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf($this->view->vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$records_per_page = 9;
$url = HOST . '/account/subscribers';
$db = Registry::get('db');

// Retrieve total count
$query = "SELECT user_id FROM " . DB_PREFIX . "subscriptions WHERE member = " . $this->view->vars->loggedInUser->userId;
$db->fetchAll($query);
$total = $db->rowCount();

// Initialize pagination
$this->view->vars->pagination = new Pagination($url, $total, $records_per_page);
$start_record = $this->view->vars->pagination->getStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$subscriberResults = $db->fetchAll($query);
$userMapper = new UserMapper();
$this->view->vars->subscribers = $userMapper->getUsersFromList(
    Functions::arrayColumn($subscriberResults, 'user_id')
);

Plugin::triggerEvent('subscribers.end');