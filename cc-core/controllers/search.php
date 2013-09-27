<?php

// Establish page variables, objects, arrays, etc
View::InitView ('search');
Plugin::triggerEvent('search.start');

View::$vars->logged_in = UserService::LoginCheck();
if (View::$vars->logged_in) {
    $userMapper = new UserMapper();
    $userMapper->getUserById(View::$vars->logged_in);
}

$keyword = null;
View::$vars->cleaned = null;
$url = HOST . '/search';
$query_string = array();
$records_per_page = 9;

// Verify a keyword was given
if (isset ($_POST['submitted_search'])) {
    View::$vars->cleaned = htmlspecialchars($_POST['keyword']);
} elseif (isset ($_GET['keyword'])) {
    View::$vars->cleaned = htmlspecialchars($_GET['keyword']);
}

$query_string['keyword'] = View::$vars->cleaned;
View::$vars->meta->title = Functions::Replace (View::$vars->meta->title, array ('keyword' => View::$vars->cleaned));
$keyword = $db->Escape (View::$vars->cleaned);

// Retrieve count of all videos
$query = "SELECT COUNT(video_id) as total FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0'";
$resultTotal = $db->fetchRow($query);

// Retrieve total count
if ($resultTotal['total'] > 20 && strlen($keyword) > 3) {
    // Use FULLTEXT query
    $query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' AND MATCH(title, tags, description) AGAINST('$keyword')";
    Plugin::triggerEvent('search.search_count');
    $result_count = $db->Query ($query);
    $count = $db->Count ($result_count);
} else {
    // Use LIKE query
    $query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' AND (title LIKE '%$keyword%' OR description LIKE '%$keyword%' OR tags LIKE '%$keyword%')";
    $result_count = $db->Query ($query);
    $count = $db->Count ($result_count);
}
Plugin::triggerEvent('search.search_count');

// Initialize pagination
$url .= (!empty ($query_string)) ? '?' . http_build_query($query_string) : '';
View::$vars->pagination = new Pagination ($url, $count, $records_per_page);
$start_record = View::$vars->pagination->GetStartRecord();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
Plugin::triggerEvent('search.search');
$searchResult = $db->fetchAll($query);
$videoMapper = new VideoMapper();
View::$vars->search_videos = $videoMapper->getMultipleVideosById(
    Functions::flattenArray($searchResult, 'video_id')
);

// Output Page
Plugin::triggerEvent('search.before_render');
View::Render ('search.tpl');