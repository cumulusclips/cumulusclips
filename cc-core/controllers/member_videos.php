<?php

### Created on March 14, 2009
### Created by Miguel A. Hurtado
### This script allows users to browse a channels' videos


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Rating');
App::LoadClass ('Pagination');
App::LoadClass ('Video');
View::InitView();


// Establish page variables, objects, arrays, etc
View::$vars->logged_in = User::LoginCheck();
if (View::$vars->logged_in) View::$vars->user = new User (View::$vars->logged_in);
View::$vars->page_title = 'Techie Videos - Videos By: ';
$records_per_page = 9;



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
    View::$vars->page_title .= View::$vars->member->username;
    $url = '/members/' . View::$vars->member->username . '/videos';
} else {
    App::Throw404();
}



         
// Retrieve total count
$query = "SELECT video_id FROM videos WHERE user_id = " . View::$vars->member->user_id . " AND status = 6";
$result_count = $db->Query ($query);
$total = $db->Count ($result_count);

// Initialize pagination
View::$vars->pagination = new Pagination ($url, $total, $records_per_page);
$start_record = View::$vars->pagination->GetStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
View::$vars->result = $db->Query ($query);


// Output Page
View::Render ('member_videos.tpl');

?>