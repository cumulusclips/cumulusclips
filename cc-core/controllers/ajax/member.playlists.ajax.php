<?php

$this->view->options->disableView = true;
$userMapper = new UserMapper();
$playlistMapper = new PlaylistMapper();
$limit = 9;
$start = 0;

// Verify a user was selected
if (!empty($_GET['userId'])) {
    $user = $userMapper->getUserById($_GET['userId']);
} else {
    App::Throw404();
}

// Check if user is valid
if (!$user || $user->status != 'active') {
    App::Throw404();
}

// Validate video limit
if (!empty($_GET['limit']) && is_numeric($_GET['limit']) && $_GET['limit'] > 0) {
    $limit = $_GET['limit'];
}

// Validate starting record
if (!empty($_GET['start']) && is_numeric($_GET['start'])) {
    $start = $_GET['start'];
}

// Retrieve playlists
$db = Registry::get('db');
$query = "SELECT playlist_id FROM " . DB_PREFIX . "playlists WHERE public = 1 and type = 'playlist' and user_id = :userId ORDER BY date_created DESC LIMIT $start, $limit";
$playlistResults = $db->fetchAll($query, array(
    ':userId' => $user->userId
));

$playlistList = $playlistMapper->getPlaylistsFromList(Functions::arrayColumn($playlistResults, 'playlist_id'));
$apiResponse = new ApiResponse();
$apiResponse->result = true;
$apiResponse->data = array('playlistList' => $playlistList);
echo json_encode($apiResponse);