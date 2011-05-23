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
//$logged_in = User::LoginCheck(HOST . '/login/');
//$admin = new User ($logged_in);
$content = 'members.tpl';
$records_per_page = 20;
$url = ADMIN . '/members.php';
$query_string = array();
$message = null;
$sub_header = null;



// Delete member
if (!empty ($_GET['delete']) && is_numeric ($_GET['delete'])) {

    // Validate user id
    if (User::Exist(array ('user_id' => $_GET['delete']))) {
//        User::Delete($_GET['id']);
        $message = 'Member has been deleted';
        $message_type = 'success';
    }

}



// Determin which type (account status) of members to display
$status = (!empty ($_GET['status'])) ? $_GET['status'] : 'active';
switch ($status) {

    case 'pending':
        $query_string['status'] = 'pending';
        $header = 'Pending Members';
        $page_title = 'Pending Members';
        break;
    case 'banned':
        $query_string['status'] = 'banned';
        $header = 'Banned Members';
        $page_title = 'Banned Members';
        break;
    default:
        $status = 'active';
        $header = 'Active Members';
        $page_title = 'Active Members';
        break;

}
$query = "SELECT user_id FROM " . DB_PREFIX . "users WHERE account_status = '$status'";



// Handle Search Member Form
if (isset ($_POST['search_submitted'])&& !empty ($_POST['search'])) {

    $like = $db->Escape (trim ($_POST['search']));
    $query_string['search'] = $like;
    $query .= " AND username LIKE '%$like%'";
    $sub_header = "Search Results for: <em>$like</em>";

} else if (!empty ($_GET['search'])) {

    $like = $db->Escape (trim ($_GET['search']));
    $query_string['search'] = $like;
    $query .= " AND username LIKE '%$like%'";
    $sub_header = "Search Results for: <em>$like</em>";

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