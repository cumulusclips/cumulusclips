<?php

Plugin::triggerEvent('browse.start');

// @deprecated Deprecated in 2.5.0, removed in 2.6.0. Use browse.start instead
Plugin::triggerEvent('videos.start');

// Verify if user is logged in
$this->authService->enforceTimeout();
$this->view->vars->loggedInUser = $this->authService->getAuthUser();

// Establish page variables, objects, arrays, etc
$load = array ('most-viewed', 'most-discussed', 'most-rated');
$this->view->vars->sub_header = null;
$this->view->vars->category_list = array();
$records_per_page = 9;
$url = HOST . '/browse';

// Retrieve Categories
$categoryService = new CategoryService();
$this->view->vars->category_list = $categoryService->getCategories();


// Prepare for default sorting ('Most Recent Videos')
$this->view->vars->sub_header = Language::GetText('most_recent');
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' ORDER BY video_id DESC";

// Retrieve videos
if (isset($_GET['category']) && preg_match('/[a-z0-9\-]+/i', $_GET['category'])) {

    $categoryMapper = new CategoryMapper();
    $category = $categoryMapper->getCategoryBySlug($_GET['category']);
    if ($category) {
        $query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' AND category_id = $category->categoryId ORDER BY video_id DESC";
        $this->view->vars->sub_header = $category->name;
        $url .= '/' . $_GET['category'];
    }

} elseif (isset($_GET['load']) && in_array($_GET['load'], $load)) {

    switch ($_GET['load']) {
        case 'most-viewed':
            $this->view->vars->sub_header = Language::GetText('most_viewed');
            $query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' ORDER BY views DESC";
            $url .= '/most-viewed';
            break;
        case 'most-discussed':
            $this->view->vars->sub_header = Language::GetText('most_discussed');
            $query = "SELECT " . DB_PREFIX . "videos.video_id, COUNT(comment_id) AS 'sum' from " . DB_PREFIX . "videos LEFT JOIN " . DB_PREFIX . "comments ON " . DB_PREFIX . "videos.video_id = " . DB_PREFIX . "comments.video_id WHERE " . DB_PREFIX . "videos.status = 'approved' AND private = '0' GROUP BY video_id ORDER BY sum DESC";
            $url .= '/most-discussed';
            break;
        case 'most-rated':
            $this->view->vars->sub_header = Language::GetText('most_rated');
            $query = "SELECT " . DB_PREFIX . "videos.video_id, COUNT(rating) AS 'rating_count', SUM(rating) as 'rating_sum' from " . DB_PREFIX . "videos LEFT JOIN " . DB_PREFIX . "ratings ON " . DB_PREFIX . "videos.video_id = " . DB_PREFIX . "ratings.video_id WHERE " . DB_PREFIX . "videos.status = 'approved' AND private = '0' GROUP BY video_id ORDER BY rating_count DESC, rating_sum DESC";
            $url .= '/most-rated';
            break;
    }
}

// Retrieve total count
$db = Registry::get('db');
$db->fetchAll($query);
$total = $db->rowCount();

// Initialize pagination
$this->view->vars->pagination = new Pagination($url, $total, $records_per_page);
$start_record = $this->view->vars->pagination->GetStartRecord();

// Retrieve limited results
$videoMapper = new VideoMapper();
$query .= " LIMIT $start_record, $records_per_page";
$result = $db->fetchAll($query);
$this->view->vars->browse_videos = $videoMapper->getVideosFromList(
    Functions::arrayColumn($result, 'video_id')
);

$this->view->vars->meta->title = $this->view->vars->meta->title . ' ' . $this->view->vars->sub_header;
Plugin::triggerEvent('browse.end');

// @deprecated Deprecated in 2.5.0, removed in 2.6.0. Use browse.end instead
Plugin::triggerEvent('videos.start');