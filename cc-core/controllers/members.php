<?php

Plugin::Trigger ('members.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();

$records_per_page = 12;
$url = HOST . '/members';

// Retrieve total count
$query = "SELECT user_id FROM " . DB_PREFIX . "users WHERE status = 'Active'";
$db->fetchAll($query);
$total = $db->rowCount();

// Initialize pagination
$view->vars->pagination = new Pagination($url, $total, $records_per_page);
$start_record = $view->vars->pagination->GetStartRecord();

// Retrieve limited results
$userMapper = new UserMapper();
$query .= " LIMIT $start_record, $records_per_page";
$userResults = $db->fetchAll($query);
$view->vars->userResults = $userMapper->getUsersFromList(
    Functions::arrayColumn($userResults, 'user_id')
);

Plugin::Trigger ('members.before_render');