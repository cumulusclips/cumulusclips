<?php

### Created on March 23, 2009
### Created by Miguel A. Hurtado
### This script searches for videos


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');
App::LoadClass ('Rating');
App::LoadClass ('Pagination');


// Establish page variables, objects, arrays, etc
View::InitView ('search');
Plugin::Trigger ('search.start');
View::$vars->logged_in = User::LoginCheck();
if (View::$vars->logged_in) View::$vars->user = new User (View::$vars->logged_in);
$keyword = NULL;
View::$vars->cleaned = NULL;
$url = HOST . '/search';
$query_string = array();
$records_per_page = 9;



// Verify a keyword was given
if (isset ($_POST['submitted_search'])) {
    View::$vars->cleaned = htmlspecialchars ($_POST['keyword']);
} elseif (isset ($_GET['keyword'])) {
    View::$vars->cleaned = htmlspecialchars ($_GET['keyword']);
}

$query_string['keyword'] = View::$vars->cleaned;
View::$vars->meta->title = Functions::Replace (View::$vars->meta->title, array ('keyword' => View::$vars->cleaned));
$keyword = $db->Escape (View::$vars->cleaned);


// Retrieve total count
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND MATCH(title, tags, description) AGAINST('$keyword')";
Plugin::Trigger ('search.search_count');
$result_count = $db->Query ($query);
$total = $db->Count ($result_count);


// Initialize pagination
$url .= (!empty ($query_string)) ? '?' . http_build_query($query_string) : '';
View::$vars->pagination = new Pagination ($url, $total, $records_per_page);
$start_record = View::$vars->pagination->GetStartRecord();


// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
Plugin::Trigger ('search.search');
$result = $db->Query ($query);
View::$vars->search_videos = array();
while ($video = $db->FetchObj ($result)) View::$vars->search_videos[] = $video->video_id;


// Output Page
Plugin::Trigger ('search.before_render');
View::Render ('search.tpl');

?>