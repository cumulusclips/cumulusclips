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
View::$vars->page_title = 'Techie Videos - ';
$limit = 9;



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
    View::$vars->page_title .= View::$vars->member->username . "'s";
} else {
    App::Throw404();
}



if (isset ($_GET['action'])) {
	
    switch ($_GET['action']) {
	
        case 'videos':

            View::$vars->viewing = 'videos';
            View::$vars->page_title .= ' Videos';
            $url = '/view-videos/' . View::$vars->member->username;
            $query = "SELECT video_id FROM videos WHERE user_id = " . View::$vars->member->user_id . " AND status = 6";
            break;
			
        case 'favorites':
		
            View::$vars->viewing = 'favorites';
            View::$vars->page_title .= ' Favorite Videos';
            $url = '/view-favorites/' . View::$vars->member->username;
            $query = "SELECT favorites.video_id FROM favorites INNER JOIN videos ON favorites.video_id = videos.video_id WHERE favorites.user_id = " . View::$vars->member->user_id . " AND status = 6";
            break;

        default:
            App::Throw404();			
	}
	
	
} else {
    App::Throw404();
}



// Retrieve total count
$result_count = $db->Query ($query);
$total = $db->Count ($result_count);

// Initialize pagination
View::$vars->pagination = new Pagination ($url, $total);
$start_record = View::$vars->pagination->GetStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $limit";
View::$vars->result = $db->Query ($query);


// Output Page
View::Render ('member_videos.tpl');

?>