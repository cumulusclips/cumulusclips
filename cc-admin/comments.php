<?php

### Created on February 28, 2009
### Created by Miguel A. Hurtado
### This script displays the site homepage


// Include required files
include ('../cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Comment');
App::LoadClass ('Video');
App::LoadClass ('Pagination');


// Establish page variables, objects, arrays, etc
Plugin::Trigger ('admin.members.start');
//$logged_in = User::LoginCheck(HOST . '/login/');
//$admin = new User ($logged_in);
$content = 'comments.tpl';
$records_per_page = 9;
$url = ADMIN . '/comments.php';
$query_string = array();
$message = null;
$sub_header = null;



### Handle "Delete" action
if (!empty ($_GET['delete']) && is_numeric ($_GET['delete'])) {

    // Validate id
    if (Comment::Exist (array ('comment_id' => $_GET['delete']))) {
        Comment::Delete ($_GET['delete']);
        $message = 'Comment has been deleted';
        $message_type = 'success';
    }

}


### Handle "Approve" action
else if (!empty ($_GET['approve']) && is_numeric ($_GET['approve'])) {

    // Validate id
    $comment = new Comment ($_GET['approve']);
    if ($comment->found) {
        $comment->Update (array ('status' => 'approved'));
        $message = 'Comment has been approved';
        $message_type = 'success';
    }

}


### Handle "Unban" action
else if (!empty ($_GET['unban']) && is_numeric ($_GET['unban'])) {

    // Validate id
    $comment = new Comment ($_GET['unban']);
    if ($comment->found) {
        $comment->Update (array ('status' => 'approved'));
        $message = 'Comment has been unbanned';
        $message_type = 'success';
    }

}


### Handle "Ban" action
else if (!empty ($_GET['ban']) && is_numeric ($_GET['ban'])) {

    // Validate id
    $comment = new Comment ($_GET['ban']);
    if ($comment->found) {
        $comment->Update (array ('status' => 'banned'));
        $message = 'Comment has been banned';
        $message_type = 'success';
    }

}




### Determine which type (status) of record to display
$status = (!empty ($_GET['status'])) ? $_GET['status'] : 'approved';
switch ($status) {

    case 'pending':
        $query_string['status'] = 'pending';
        $header = 'Pending Comments';
        $page_title = 'Pending Comments';
        $where = "'pending'";
        break;
    case 'banned':
        $query_string['status'] = 'banned';
        $header = 'Banned Comments';
        $page_title = 'Banned Comments';
        $where = "'banned', 'banned_chain'";
        break;
    default:
        $status = 'approved';
        $header = 'Approved Comments';
        $page_title = 'Approved Comments';
        $where = "'approved'";
        break;

}
$query = "SELECT comment_id FROM " . DB_PREFIX . "comments WHERE status IN ($where)";




### Handle Search Records Form
if (isset ($_POST['search_submitted'])&& !empty ($_POST['search'])) {

    $like = $db->Escape (trim ($_POST['search']));
    $query_string['search'] = $like;
    $query .= " AND comments LIKE '%$like%'";
    $sub_header = "Search Results for: <em>$like</em>";

} else if (!empty ($_GET['search'])) {

    $like = $db->Escape (trim ($_GET['search']));
    $query_string['search'] = $like;
    $query .= " AND comments LIKE '%$like%'";
    $sub_header = "Search Results for: <em>$like</em>";

}




// Retrieve total count
$result_count = $db->Query ($query);
$total = $db->Count ($result_count);

// Initialize pagination
$url .= (!empty ($query_string)) ? '?' . http_build_query($query_string) : '';
$pagination = new Pagination ($url, $total, $records_per_page, false);
$start_record = $pagination->GetStartRecord();
$_SESSION['list_page'] = $pagination->GetURL();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$result = $db->Query ($query);


// Output Page
include (THEME_PATH . '/admin.layout.tpl');

?>