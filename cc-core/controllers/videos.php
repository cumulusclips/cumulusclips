<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/config/bootstrap.php');
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
$load = array ('most-viewed', 'most-discussed', 'most-rated');
View::$vars->sub_header = null;
View::$vars->category_list = array();
$records_per_page = 9;
$url = HOST . '/videos';



// Retrieve Categories	
$query = "SELECT cat_id FROM " . DB_PREFIX . "categories ORDER BY cat_name ASC";
$result_cats = $db->Query ($query);
while ($row = $db->FetchObj ($result_cats)) View::$vars->category_list[] = $row->cat_id;



// Prepare for default sorting ('Most Recent Videos')
View::$vars->sub_header = Language::GetText ('most_recent');
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' ORDER BY video_id DESC";



// Retrieve videos
if (isset ($_GET['category']) && preg_match ('/[a-z0-9\-]+/i', $_GET['category'])) {

    $id = Category::Exist (array ('slug' => $_GET['category']));
    if ($id) {
        $category = new Category ($id);
        $query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND cat_id = $id ORDER BY video_id DESC";
        View::$vars->sub_header = $category->cat_name;
        $url .= '/' . $_GET['category'];
    }
	
} elseif (isset ($_GET['load']) && in_array ($_GET['load'], $load)) {

    switch ($_GET['load']) {

        case 'most-viewed':

            View::$vars->sub_header = Language::GetText ('most_viewed');
            $query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' ORDER BY views DESC";
            $url .= '/most-viewed';
            break;

        case 'most-discussed':

            View::$vars->sub_header = Language::GetText ('most_discussed');
            $query = "SELECT " . DB_PREFIX . "videos.video_id, COUNT(comment_id) AS 'sum' from " . DB_PREFIX . "videos LEFT JOIN " . DB_PREFIX . "comments ON " . DB_PREFIX . "videos.video_id = " . DB_PREFIX . "comments.video_id WHERE " . DB_PREFIX . "videos.status = 'approved' GROUP BY video_id ORDER BY sum DESC";
            $url .= '/most-discussed';
            break;

        case 'most-rated':

            View::$vars->sub_header = Language::GetText ('most_rated');
            $query = "SELECT " . DB_PREFIX . "videos.video_id, COUNT(rating) AS 'rating_count', SUM(rating) as 'rating_sum' from " . DB_PREFIX . "videos LEFT JOIN " . DB_PREFIX . "ratings ON " . DB_PREFIX . "videos.video_id = " . DB_PREFIX . "ratings.video_id WHERE " . DB_PREFIX . "videos.status = 'approved' GROUP BY video_id ORDER BY rating_count DESC, rating_sum DESC";
            $url .= '/most-rated';
            break;

    }
	
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
View::$vars->meta->title .= ' ' . View::$vars->sub_header;
Plugin::Trigger ('videos.before_render');
View::Render ('videos.tpl');

?>