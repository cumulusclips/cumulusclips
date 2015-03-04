<?php

$this->view->options->disableView = true;
$db = Registry::get('db');
$videoMapper = new VideoMapper();
$limit = 20;
$start = 0;


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

// Retrieve search result video list
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' AND gated = '0' AND MATCH(title, tags, description) AGAINST(:keyword) LIMIT $start, $limit";
$videoResults = $db->fetchAll($query, array(':keyword' => $keyword));
$searchVideos = $videoMapper->getVideosFromList(
    Functions::arrayColumn($videoResults, 'video_id')
);
echo json_encode(array('result' => true, 'message' => '', 'other' => array('videoList' => $searchVideos)));