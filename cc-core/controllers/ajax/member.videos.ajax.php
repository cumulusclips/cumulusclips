<?php

// Verify if user registrations are enabled
$config = Registry::get('config');
if (!$config->enableUserUploads) App::throw404();

$this->view->options->disableView = true;
$userMapper = new UserMapper();
$videoMapper = new VideoMapper();
$videoService = new VideoService();
$limit = 9;
$start = 0;

// Verify a user was selected
if (!empty($_GET['userId'])) {
    $user = $userMapper->getUserById($_GET['userId']);
} else {
    App::throw404();
}

// Check if user is valid
if (!$user || $user->status != 'active') {
    App::throw404();
}

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
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' AND user_id = :userId ORDER BY date_created DESC LIMIT $start, $limit";
$videoResults = $db->fetchAll($query, array(
    ':userId' => $user->userId
));

$videoList = $videoMapper->getVideosFromList(Functions::arrayColumn($videoResults, 'video_id'));
echo json_encode(array('result' => true, 'message' => '', 'other' => array('videoList' => $videoList)));