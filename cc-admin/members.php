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
$logged_in = User::LoginCheck(HOST . '/login/');
$admin = new User ($logged_in);
$page_title = 'Browse ';
$content = 'members.tpl';
$records_per_page = 20;
$url = ADMIN . '/members.php';
$query_string = array();



// Handle Search Member Form
if (isset ($_POST['search_submitted'])&& !empty ($_POST['username'])) {

    $like = $db->Escape (trim ($_POST['username']));
    $query_string['username'] = $like;
    $query = "SELECT user_id FROM " . DB_PREFIX . "users WHERE username LIKE '%$like%'";
    $page_title = 'Search Members';
    $header = 'Search';

} else if (!empty ($_GET['username'])) {

    $like = $db->Escape (trim ($_GET['username']));
    $query_string['username'] = $like;
    $query = "SELECT user_id FROM " . DB_PREFIX . "users WHERE username LIKE '%$like%'";
    $page_title = 'Search Members';
    $header = 'Search Members';

} else {

    $status = (!empty ($_GET['status'])) ? $_GET['status'] : 'active';
    switch ($status) {

        case 'pending':
            $query_string['status'] = 'pending';
            $header = 'Browse Pending';
            $page_title = 'Browse Pending';
            break;
        case 'banned':
            $query_string['status'] = 'banned';
            $header = 'Browse Banned';
            $page_title = 'Browse Banned';
            break;
        default:
            $status = 'active';
            $header = 'Browse Active';
            $page_title .= 'Active Members';
            break;

    }
    $query = "SELECT user_id FROM " . DB_PREFIX . "users WHERE account_status = '$status'";

}








// Delete member
if (!empty ($_GET['delete']) && is_numeric ($_GET['delete'])) {

    // Validate user id
    if (User::Exist(array ('user_id' => $_GET['delete']))) {
//        User::Delete($_GET['id']);
        exit('Delete '.$_GET['delete']);
    }

}






// Retrieve total count
$result_count = $db->Query ($query);
$total = $db->Count ($result_count);

// Initialize pagination
$url .= (!empty ($query_string)) ? '?' . http_build_query($query_string) : '';
$pagination = new Pagination ($url, $total, $records_per_page, false);
$start_record = $pagination->GetStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$result = $db->Query ($query);


// Output Page
include (THEME_PATH . '/admin.layout.tpl');

?>