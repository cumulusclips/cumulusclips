<?php

### Created on March 3, 2009
### Created by Miguel A. Hurtado
### This script allows users to browse all videos


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');
App::LoadClass ('Rating');
App::LoadClass ('Category');
App::LoadClass ('Pagination');


// Establish page variables, objects, arrays, etc
View::InitView ('videos');
Plugin::Trigger ('videos.start');
View::$vars->logged_in = User::LoginCheck();
if (View::$vars->logged_in) View::$vars->user = new User (View::$vars->logged_in);
$load = array ('recent', 'most-viewed', 'most-discussed');
View::$vars->category = null;
$records_per_page = 9;
$url = HOST . '/videos';



// Retrieve Categories	
$query = "SELECT cat_id, cat_name FROM " . DB_PREFIX . "categories ORDER BY cat_name ASC";
View::$vars->result_cats = $db->Query ($query);



// Retrieve videos
if (isset ($_GET['category']) && preg_match ('/a-z0-9+/i', $_GET['category'])) {

    $where = "status = 'approved'";
    $cat_dashed = $_GET['category'];
    $cat_undashed = str_replace ('-',' ', $cat_dashed);
    $id = Category::Exist (array ('cat_name' => $cat_undashed), $db);
    if ($id) {
        $where .= " AND cat_id = $id";
        View::$vars->category = $cat_undashed;
        View::$vars->meta->title = Functions::Replace (View::$vars->meta->title, array ('browsing' => View::$vars->category));
        $url .= '/' . $cat_dashed;
    }
    $query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE $where ORDER BY video_id DESC";
	
} elseif (isset ($_GET['load']) && in_array ($_GET['load'], $load)) {

    switch ($_GET['load']) {

        case 'recent':

            $query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' ORDER BY video_id DESC";
            View::$vars->meta->title = Functions::Replace (View::$vars->meta->title, array ('browsing' => 'Recent'));
            $url .= '/recent';
            break;

        case 'most-viewed':

            $query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' ORDER BY views DESC";
            View::$vars->meta->title = Functions::Replace (View::$vars->meta->title, array ('browsing' => 'Most Viewed'));
            $url .= '/most-viewed';
            break;

        case 'most-discussed':

            $query = "SELECT " . DB_PREFIX . "videos.video_id, COUNT(comment_id) AS 'sum' from " . DB_PREFIX . "videos LEFT JOIN " . DB_PREFIX . "comments ON " . DB_PREFIX . "videos.video_id = " . DB_PREFIX . "comments.video_id WHERE video.status = 'approved' GROUP BY video_id ORDER BY sum DESC";
            View::$vars->meta->title = Functions::Replace (View::$vars->meta->title, array ('browsing' => 'Most Discussed'));
            $url .= '/most-discussed';
            break;

    }
	
} else {
    $query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' ORDER BY video_id DESC";
    View::$vars->meta->title = Functions::Replace (View::$vars->meta->title, array ('browsing' => 'All'));
}

// Retrieve total count
$result_count = $db->Query ($query);
$total = $db->Count ($result_count);

// Initialize pagination
View::$vars->pagination = new Pagination ($url, $total, $records_per_page);
$start_record = View::$vars->pagination->GetStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$result = $db->Query ($query);
View::$vars->browse_videos = array();
while ($video = $db->FetchObj ($result)) View::$vars->browse_videos[] = $video->video_id;


// Output Page
Plugin::Trigger ('videos.before_render');
View::Render ('videos.tpl');

?>