<?php

// Establish page variables, objects, arrays, etc
View::InitView ('members');
Plugin::Trigger ('members.start');

// Verify if user is logged in
$userService = new UserService();
View::$vars->loggedInUser = $userService->loginCheck();

$records_per_page = 12;
$url = HOST . '/members';

// Retrieve total count
$query = "SELECT user_id FROM " . DB_PREFIX . "users WHERE status = 'Active'";
$db->fetchAll($query);
$total = $db->rowCount();

// Initialize pagination
View::$vars->pagination = new Pagination($url, $total, $records_per_page);
$start_record = View::$vars->pagination->GetStartRecord();

// Retrieve limited results
$userMapper = new UserMapper();
$query .= " LIMIT $start_record, $records_per_page";
$userResults = $db->fetchAll($query);
View::$vars->userResults = $userMapper->getMultipleUsersById(
    Functions::flattenArray($userResults, 'user_id')
);

// Output Page
Plugin::Trigger ('members.before_render');
View::Render ('members.tpl');