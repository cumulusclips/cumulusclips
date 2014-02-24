<?php

// Init view
$view->InitView ('playlists_edit');
Plugin::triggerEvent('edit_video.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf($view->vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$playlistMapper = new PlaylistMapper();
$videoMapper = new VideoMapper();
$playlistService = new PlaylistService();
$view->vars->message = null;

// Validate requested playlist
if (!empty($_GET['playlist_id']) && is_numeric ($_GET['playlist_id']) && $_GET['playlist_id'] > 0) {
    $view->vars->playlist = $playlistMapper->getPlaylistByCustom(array(
        'user_id' => $view->vars->loggedInUser->userId,
        'playlist_id' => $_GET['playlist_id']
    ));
    if (!$view->vars->playlist) {
        header('Location: ' . HOST . '/myaccount/playlists/');
        exit();
    }
}

// Handle create new playlist if submitted
if (!empty($_POST['submitted'])) {
    
    // Validate playlist name
    if (!empty($_POST['name'])) {
        $view->vars->playlist->name = trim($_POST['name']);
    } else {
        $view->vars->message = Language::GetText('error_playlist_name');
        $view->vars->message_type = 'errors';
    }
    
    // Validate playlist visibility
    if (!empty($_POST['visibility']) && $_POST['visibility'] == 'public') {
        $view->vars->playlist->public = true;
    } else {
        $view->vars->playlist->public = false;
    }
    
    // Create playlist if no errors were found
    if (!empty($view->vars->playlist->name) && isset($view->vars->playlist->public)) {
        $playlistMapper->save($view->vars->playlist);
        $view->vars->message = Language::GetText('success_playlist_updated');
        $view->vars->message_type = 'success';
    }
}

// Handle remove video from playlist if submitted
if (!empty($_GET['remove']) && is_numeric ($_GET['remove']) && $_GET['remove'] > 0) {
    $video = $videoMapper->getVideoById($_GET['remove']);
    if ($video && $playlistService->checkListing($video, $view->vars->playlist)) {
        $view->vars->playlist = $playlistService->deleteVideo($video, $view->vars->playlist);
        $view->vars->message = Language::GetText('success_playlist_video_removed');
        $view->vars->message_type = 'success';
        Plugin::Trigger ('myfavorites.remove_favorite');
    }
}

// Prepare page for render
$view->vars->meta->title = Functions::Replace($view->vars->meta->title, array ('playlist_name' => $playlistService->getPlaylistName($view->vars->playlist)));
$view->vars->videoList = $playlistService->getPlaylistVideos($view->vars->playlist);

// Output page
Plugin::Trigger ('myfavorites.before_render');
$view->Render ('myaccount/playlists_edit.tpl');