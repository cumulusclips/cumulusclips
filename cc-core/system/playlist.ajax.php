<?php

// Init view
Plugin::Trigger ('favorite.ajax.start');

// Verify if user is logged in
$userService = new UserService();
//$loggedInUser = $userService->loginCheck();
$userMapper = new UserMapper();
$loggedInUser = $userMapper->getUserById(1);
Plugin::Trigger ('favorite.ajax.login_check');

// Establish page variables, objects, arrays, etc
$videoMapper = new VideoMapper();
$playlistMapper = new PlaylistMapper();
$playlistService = new PlaylistService();

// Verify a valid video was provided
if (empty ($_POST['video_id']) || !is_numeric ($_POST['video_id'])) App::Throw404();
$video = $videoMapper->getVideoByCustom(array('video_id' => $_POST['video_id'], 'status' => 'approved'));
if (!$video) App::Throw404();

// Verify user is logged in
if (!$loggedInUser) {
    echo json_encode(array('result' => 0, 'msg' => (string) Language::GetText('error_playlist_login')));
    exit();
}

// Determine if playlist is being created or added to
if (empty($_POST['action']) || !in_array($_POST['action'], array('add', 'create'))) App::Throw404();
if ($_POST['action'] == 'add') {
    
    // Verify a valid playlist was provided
    if (empty ($_POST['playlist_id']) || !is_numeric ($_POST['playlist_id'])) App::Throw404();
    $playlist = $playlistMapper->getPlaylistByCustom(array('playlist_id' => $_POST['playlist_id'], 'user_id' => $loggedInUser->userId));
    if (!$playlist) App::Throw404();

    // Add video to playlist if not already in list
    if (!$playlistService->checkListing($video, $playlist)) {
        $playlistService->addVideoToPlaylist($video, $playlist);
        Plugin::Trigger ('favorite.ajax.favorite_video');
        $playlistName = $playlistService->getPlaylistName($playlist);
        echo json_encode (array ('result' => 1, 'msg' => (string) Language::GetText('success_playlist_added', array('list_name' => $playlistName))));
        exit();
    } else {
        echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText('error_playlist_duplicate')));
        exit();
    }
    
} else {
    
    $playlist = new Playlist();
    $playlist->userId = $loggedInUser->userId;
    
    // Validate playlist name
    if (!empty($_POST['name'])) {
        $playlist->name = trim($_POST['name']);
    } else {
        echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText('error_playlist_name')));
        exit();
    }
    
    // Validate playlist visibility
    if (!empty($_POST['public']) && $_POST['public'] == 'true') {
        $playlist->public = true;
    } else {
        $playlist->public = false;
    }
    
    $playlistId = $playlistMapper->save($playlist);
    $newPlaylist = $playlistMapper->getPlaylistById($playlistId);
    $playlistService->addVideoToPlaylist($video, $newPlaylist);
    echo json_encode (array ('result' => 1, 'msg' => (string) Language::GetText('success_playlist_created'), 'other' => array('name' => $newPlaylist->name)));
    exit();
}