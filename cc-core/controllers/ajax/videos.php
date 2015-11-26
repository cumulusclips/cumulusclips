<?php

$this->view->options->disableView = true;
$videoMapper = new VideoMapper();
$limit = 20;
$start = 0;

// Validate video limit
if (!empty($_GET['limit']) && is_numeric($_GET['limit']) && $_GET['limit'] > 0) {
    $limit = $_GET['limit'];
}

// Validate starting record
if (!empty($_GET['start']) && is_numeric($_GET['start'])) {
    $start = $_GET['start'];
}

// Retrieve video list
$db = Registry::get('db');
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' AND gated = '0' ORDER BY video_id DESC LIMIT $start, $limit";
$videoResults = $db->fetchAll($query);

$videoList = $videoMapper->getVideosFromList(Functions::arrayColumn($videoResults, 'video_id'));
echo json_encode(array('result' => true, 'message' => '', 'other' => array('videoList' => $videoList)));