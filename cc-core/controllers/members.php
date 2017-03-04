<?php

Plugin::triggerEvent('members.start');

// Verify if user is logged in
$this->authService->enforceTimeout();
$this->view->vars->loggedInUser = $this->authService->getAuthUser();

$records_per_page = 12;
$url = HOST . '/members';

// Retrieve total count
$db = Registry::get('db');
$query = "SELECT user_id FROM " . DB_PREFIX . "users WHERE status = 'Active'";
$db->fetchAll($query);
$total = $db->rowCount();

// Initialize pagination
$this->view->vars->pagination = new Pagination($url, $total, $records_per_page);
$start_record = $this->view->vars->pagination->GetStartRecord();

// Retrieve limited results
$userMapper = new UserMapper();
$query .= " LIMIT $start_record, $records_per_page";
$userResults = $db->fetchAll($query);
$this->view->vars->userResults = $userMapper->getUsersFromList(
    Functions::arrayColumn($userResults, 'user_id')
);

Plugin::triggerEvent('members.end');