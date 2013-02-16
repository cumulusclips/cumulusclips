<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Rating');
App::LoadClass ('Pagination');
App::LoadClass ('Video');


// Establish page variables, objects, arrays, etc
View::InitView ('member_videos');
Plugin::Trigger ('member_videos.start');
View::$vars->logged_in = User::LoginCheck();
if (View::$vars->logged_in) View::$vars->user = new User (View::$vars->logged_in);
$records_per_page = 9;
$url = HOST . '/members';



// Verify Member was supplied
if (isset ($_GET['username'])) {
    $data = array ('username' => $_GET['username']);
    $id = User::Exist ($data);
} else {
    App::Throw404();
}



// Verify Member exists
if ($id) {
    View::$vars->member = new User ($id);
    View::$vars->meta->title = Functions::Replace(View::$vars->meta->title, array ('member' => View::$vars->member->username));
    $url .= '/' . View::$vars->member->username . '/videos';
} else {
    App::Throw404();
}



         
// Retrieve total count
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE user_id = " . View::$vars->member->user_id . " AND status = 'approved' AND private = '0'";
$result_count = $db->Query ($query);
$total = $db->Count ($result_count);

// Initialize pagination
View::$vars->pagination = new Pagination ($url, $total, $records_per_page);
$start_record = View::$vars->pagination->GetStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$result = $db->Query ($query);
View::$vars->video_list = array();
while ($video = $db->FetchObj ($result)) {
    View::$vars->video_list[] = $video->video_id;
}

// Output Page
Plugin::Trigger ('member_videos.before_render');
View::Render ('member_videos.tpl');

?>