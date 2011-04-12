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
View::InitView();


// Establish page variables, objects, arrays, etc
View::$vars->logged_in = User::LoginCheck();
if (View::$vars->logged_in) View::$vars->user = new User (View::$vars->logged_in);
View::$vars->page_title = 'Techie Videos - Browse ';
$cat_exp = '^[A-Za-z0-9-]+$';
$load = array ('recent', 'most-viewed', 'most-discussed');
View::$vars->category = null;
$records_per_page = 9;
$url = HOST . '/videos';



// Retrieve Categories	
$query = "SELECT cat_id, cat_name FROM categories ORDER BY cat_name ASC";
View::$vars->result_cats = $db->Query ($query);



// Retrieve videos
if (isset ($_GET['category']) && eregi ($cat_exp, $_GET['category'])) {

    $where = "status = 6";
    $cat_dashed = $_GET['category'];
    $cat_undashed = str_replace ('-',' ', $cat_dashed);
    $id = Category::Exist (array ('cat_name' => $cat_undashed), $db);
    if ($id) {
        $where .= " AND cat_id = $id";
        View::$vars->category = $cat_undashed;
        View::$vars->page_title .= View::$vars->category . ' Videos';
        $url .= '/' . $cat_dashed;
    }
    $query = "SELECT video_id FROM videos WHERE $where ORDER BY video_id DESC";
	
} elseif (isset ($_GET['load']) && in_array ($_GET['load'], $load)) {

    switch ($_GET['load']) {

        case 'recent':

            $query = "SELECT video_id FROM videos WHERE status = 6 ORDER BY video_id DESC";
            View::$vars->page_title .= 'Most Recent Tech videos';
            $url .= '/recent';
            break;

        case 'most-viewed':

            $query = "SELECT video_id FROM videos WHERE status = 6 ORDER BY views DESC";
            View::$vars->page_title .= 'Most Viewed Tech videos';
            $url .= '/most-viewed';
            break;

        case 'most-discussed':

            $query = "SELECT videos.video_id, COUNT(comment_id) AS 'sum' from videos LEFT JOIN video_comments ON videos.video_id = video_comments.video_id GROUP BY video_id ORDER BY sum DESC";
            View::$vars->page_title .= 'Most Discussed Tech videos';
            $url .= '/most-discussed';
            break;

    }
	
} else {
    $query = "SELECT video_id FROM videos WHERE status = 6 ORDER BY video_id DESC";
    View::$vars->page_title .= 'Tech videos';
}

// Retrieve total count
$result_count = $db->Query ($query);
$total = $db->Count ($result_count);

// Initialize pagination
View::$vars->pagination = new Pagination ($url, $total, $records_per_page);
$start_record = View::$vars->pagination->GetStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
View::$vars->result = $db->Query ($query);


// Output Page
View::Render ('videos.tpl');

?>