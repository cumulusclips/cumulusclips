<?php

$this->view->options->disableView = true;
$db = Registry::get('db');
$videoMapper = new VideoMapper();
$limit = 20;
$start = 0;

// Validate search keyword
if (!empty($_POST['keyword'])) {
    $keyword = trim($_POST['keyword']);
} else {
    App::throw404();
}

// Validate video limit
if (!empty($_POST['limit']) && is_numeric($_POST['limit']) && $_POST['limit'] > 0) {
    $limit = $_POST['limit'];
}

// Validate starting record
if (!empty($_POST['start']) && is_numeric($_POST['start'])) {
    $start = $_POST['start'];
}

// Retrieve count of all videos
$query = "SELECT COUNT(video_id) as total FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0'";
$resultTotal = $db->fetchRow($query);
$total = (int) $resultTotal['total'];

// Retrieve total count
$keyword = $_POST['keyword'];
if ($total > 20 && strlen($keyword) > 3) {
    // Use FULLTEXT query
    $query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' AND MATCH(title, tags, description) AGAINST(:keyword) LIMIT $start, $limit";
} else {
    // Use LIKE query
    $keyword = '%' . $keyword . '%';
    $query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' AND (title LIKE :keyword OR description LIKE :keyword OR tags LIKE :keyword) LIMIT $start, $limit";
}

// Retrieve search results
$searchResults = $db->fetchAll($query, array(':keyword' => $keyword));
$searchVideos = $videoMapper->getVideosFromList(
    Functions::arrayColumn($searchResults, 'video_id')
);
echo json_encode(array('result' => true, 'message' => '', 'other' => array('videoList' => $searchVideos)));