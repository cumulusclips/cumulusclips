<?php

// Include required files
include_once (dirname (dirname (dirname (__FILE__))) . '/config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');
App::LoadClass ('Pagination');
App::LoadClass ('Rating');


// Establish page variables, objects, arrays, etc
View::InitView ('myvideos');
Plugin::Trigger ('myvideos.start');
Functions::RedirectIf (View::$vars->logged_in = User::LoginCheck(), HOST . '/login/');
View::$vars->user = new User (View::$vars->logged_in);
$records_per_page = 9;
$url = HOST . '/myaccount/myvideos';
View::$vars->message = null;





/***********************
Handle Form if submitted
***********************/

if (isset ($_GET['vid']) && is_numeric ($_GET['vid'])) {

    $data = array ('user_id' => View::$vars->user->user_id, 'video_id' => $_GET['vid']);
    $video_id = Video::Exist ($data);
    if ($video_id) {
        Video::Delete ($video_id);
        View::$vars->message = Language::GetText('success_video_deleted');
        View::$vars->message_type = 'success';
        Plugin::Trigger ('myvideos.delete_video');
    }

}


// Retrieve total count
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE user_id = " . View::$vars->user->user_id . " AND status = 'approved' ORDER BY date_created DESC";
$result_count = $db->Query ($query);
$total = $db->Count ($result_count);

// Initialize pagination
View::$vars->pagination = new Pagination ($url, $total, $records_per_page);
$start_record = View::$vars->pagination->GetStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
View::$vars->result = $db->Query ($query);


// Output page
Plugin::Trigger ('myvideos.before_render');
View::Render ('myaccount/myvideos.tpl');

?>