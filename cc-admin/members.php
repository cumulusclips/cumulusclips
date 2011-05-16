<?php

### Created on February 28, 2009
### Created by Miguel A. Hurtado
### This script displays the site homepage


// Include required files
include ('../cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Pagination');


// Establish page variables, objects, arrays, etc
Plugin::Trigger ('admin.members.start');
$logged_in = User::LoginCheck();
if ($logged_in) $admin = new User ($logged_in);
$page_title = 'Browse ';
$content = 'members.tpl';
$records_per_page = 20;
$url = array (ADMIN . '/members.php');

$status = (!empty ($_GET['status'])) ? $_GET['status'] : 'Active';

switch ($status) {

    case 'Pending':
        break;
    case 'Banned':
        break;
    default:
        $status = 'Active';
        $page_title .= 'Active Members';
        break;


}


// Delete member
if (!empty ($_GET['action']) && $_GET['action'] = 'delete') {

    // Validate user id
    if (is_numeric ($_GET['id']) && User::Exist(array ('user_id' => $_GET['id']))) {
        User::Delete($_GET['id']);
    }

}



// General Member Query
$query = "SELECT user_id FROM " . DB_PREFIX . "users WHERE account_status = '$status'";



// Handle Search Member Form
if (isset ($_POST['search_submitted'])&& !empty ($_POST['username'])) {
    $like = $db->Escape (trim ($_POST['username']));
    $url[] = "&username=$like";
    $query = "SELECT user_id FROM " . DB_PREFIX . "users WHERE username LIKE '%$like%'";
} else if (!empty ($_GET['username'])) {
    $like = $db->Escape (trim ($_POST['username']));
    $url[] = "&username=$like";
    $query = "SELECT user_id FROM " . DB_PREFIX . "users WHERE username LIKE '%$like%'";
}



// Retrieve total count
$result_count = $db->Query ($query);
$total = $db->Count ($result_count);

// Initialize pagination
$pagination = new Pagination ($url, $total, $records_per_page, false);
$start_record = $pagination->GetStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$result = $db->Query ($query);


// Output Page
include (THEME_PATH . '/admin.layout.tpl');

?>