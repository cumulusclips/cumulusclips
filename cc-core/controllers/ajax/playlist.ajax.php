<?php

// Verify if user is logged in
$userService = new UserService();
$loggedInUser = $userService->loginCheck();

// Establish page variables, objects, arrays, etc
$this->view->options->disableView = true;
$videoMapper = new VideoMapper();
$playlistMapper = new PlaylistMapper();
$playlistService = new PlaylistService();

// Verify a valid video was provided
if (empty($_POST['video_id']) || !is_numeric($_POST['video_id'])) App::throw404();
$video = $videoMapper->getVideoByCustom(array('video_id' => $_POST['video_id'], 'status' => 'approved'));
if (!$video) App::throw404();

// Verify user is logged in
if (!$loggedInUser) {
    echo json_encode(array(
        'result' => false,
        'message' => (string) Language::getText('playlist_login', array('host' => HOST)),
        'other' => array('status' => 'LOGIN')
    ));
    exit();
}

// Determine if playlist is being created or added to
if (empty($_POST['action']) || !in_array($_POST['action'], array('add', 'create'))) App::throw404();
if ($_POST['action'] == 'add') {
    
    // Verify a valid playlist was provided
    if (empty($_POST['playlist_id']) || !is_numeric($_POST['playlist_id'])) App::Throw404();
    $playlist = $playlistMapper->getPlaylistByCustom(array('playlist_id' => $_POST['playlist_id'], 'user_id' => $loggedInUser->userId));
    if (!$playlist) App::throw404();

    // Add video to playlist if not already in list
    if (!$playlistService->checkListing($video, $playlist)) {
        $playlistService->addVideoToPlaylist($video, $playlist);
        $playlistName = $playlistService->getPlaylistName($playlist);
        $message = (!empty($_POST['shortText'])) ? 'success_playlist_added_short' : 'success_playlist_added';
        echo json_encode(array(
            'result' => true,
            'message' => (string) Language::getText($message, array('list_name' => $playlistName)),
            'other' => array('count' => count($playlist->entries)+1)
        ));
        exit();
    } else {
        echo json_encode(array(
            'result' => false,
            'message' => (string) Language::GetText('error_playlist_duplicate'),
            'other' => array('status' => 'DUPLICATE')
        ));
        exit();
    }
    
} else {
    
    $playlist = new Playlist();
    $playlist->userId = $loggedInUser->userId;
    
    // Validate playlist name
    if (!empty($_POST['playlist_name'])) {
        $playlist->name = trim($_POST['playlist_name']);
    } else {
        echo json_encode(array(
            'result' => false,
            'message' => (string) Language::getText('error_playlist_name'),
            'other' => array('status' => 'DATA')
        ));
        exit();
    }
    
    // Validate playlist visibility
    if (!empty($_POST['playlist_visibility']) && $_POST['playlist_visibility'] == 'public') {
        $playlist->public = true;
    } else {
        $playlist->public = false;
    }
    
    $playlistId = $playlistMapper->save($playlist);
    $newPlaylist = $playlistMapper->getPlaylistById($playlistId);
    $playlistService->addVideoToPlaylist($video, $newPlaylist);
    echo json_encode (array (
        'result' => true,
        'message' => (string) Language::GetText('success_playlist_created'),
        'other' => array('name' => $newPlaylist->name, 'count' => 1, 'playlistId' => $playlistId)));
    exit();
}