<?php

// Establish page variables, objects, arrays, etc
View::InitView('videos');
Plugin::Trigger('videos.start');

// Verify if user is logged in
$userService = new UserService();
View::$vars->loggedInUser = $userService->loginCheck();

$load = array ('most-viewed', 'most-discussed', 'most-rated');
View::$vars->sub_header = null;
View::$vars->category_list = array();
$records_per_page = 9;
$url = HOST . '/videos';

// Retrieve Categories	
$categoryMapper = new CategoryMapper();
$query = "SELECT category_id FROM " . DB_PREFIX . "categories ORDER BY name ASC";
$categoryResults = $db->fetchAll($query);
View::$vars->category_list = $categoryMapper->getMultipleCategoriesById(
    Functions::flattenArray($categoryResults, 'category_id')
);

// Prepare for default sorting ('Most Recent Videos')
View::$vars->sub_header = Language::GetText('most_recent');
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' ORDER BY video_id DESC";

// Retrieve videos
if (isset($_GET['category']) && preg_match('/[a-z0-9\-]+/i', $_GET['category'])) {

    $category = $categoryMapper->getCategoryBySlug($_GET['category']);
    if ($category) {
        $query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' AND category_id = $category->categoryId ORDER BY video_id DESC";
        View::$vars->sub_header = $category->name;
        $url .= '/' . $_GET['category'];
    }
	
} elseif (isset($_GET['load']) && in_array($_GET['load'], $load)) {

    switch ($_GET['load']) {
        case 'most-viewed':
            View::$vars->sub_header = Language::GetText('most_viewed');
            $query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' ORDER BY views DESC";
            $url .= '/most-viewed';
            break;
        case 'most-discussed':
            View::$vars->sub_header = Language::GetText('most_discussed');
            $query = "SELECT " . DB_PREFIX . "videos.video_id, COUNT(comment_id) AS 'sum' from " . DB_PREFIX . "videos LEFT JOIN " . DB_PREFIX . "comments ON " . DB_PREFIX . "videos.video_id = " . DB_PREFIX . "comments.video_id WHERE " . DB_PREFIX . "videos.status = 'approved' AND private = '0' GROUP BY video_id ORDER BY sum DESC";
            $url .= '/most-discussed';
            break;
        case 'most-rated':
            View::$vars->sub_header = Language::GetText('most_rated');
            $query = "SELECT " . DB_PREFIX . "videos.video_id, COUNT(rating) AS 'rating_count', SUM(rating) as 'rating_sum' from " . DB_PREFIX . "videos LEFT JOIN " . DB_PREFIX . "ratings ON " . DB_PREFIX . "videos.video_id = " . DB_PREFIX . "ratings.video_id WHERE " . DB_PREFIX . "videos.status = 'approved' AND private = '0' GROUP BY video_id ORDER BY rating_count DESC, rating_sum DESC";
            $url .= '/most-rated';
            break;
    }
}

// Retrieve total count
$db->fetchAll($query);
$total = $db->rowCount();

// Initialize pagination
View::$vars->pagination = new Pagination($url, $total, $records_per_page);
$start_record = View::$vars->pagination->GetStartRecord();

// Retrieve limited results
$videoMapper = new VideoMapper();
$query .= " LIMIT $start_record, $records_per_page";
$result = $db->fetchAll($query);
View::$vars->browse_videos = $videoMapper->getMultipleVideosById(
    Functions::flattenArray($result, 'video_id')
);

// Output Page
View::$vars->meta->title = View::$vars->meta->title . ' ' . View::$vars->sub_header;
Plugin::Trigger('videos.before_render');
View::Render('videos.tpl');