<?php

// Verify if user is logged in
$userService = new UserService();
$this->view->vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf($this->view->vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$playlistMapper = new PlaylistMapper();
$videoMapper = new VideoMapper();
$playlistService = new PlaylistService();
$this->view->vars->message = null;

// Validate requested playlist
if (!empty($_GET['playlist_id']) && is_numeric ($_GET['playlist_id']) && $_GET['playlist_id'] > 0) {
    $this->view->vars->playlist = $playlistMapper->getPlaylistByCustom(array(
        'user_id' => $this->view->vars->loggedInUser->userId,
        'playlist_id' => $_GET['playlist_id']
    ));
    if (!$this->view->vars->playlist) {
        header('Location: ' . HOST . '/account/playlists/');
        exit();
    }
}

// Handle create new playlist if submitted
if (!empty($_POST['submitted'])) {
    
    // Validate playlist name
    if (!empty($_POST['name'])) {
        $this->view->vars->playlist->name = trim($_POST['name']);
    } else {
        $this->view->vars->message = Language::GetText('error_playlist_name');
        $this->view->vars->message_type = 'errors';
    }
    
    // Validate playlist visibility
    if (!empty($_POST['visibility']) && $_POST['visibility'] == 'public') {
        $this->view->vars->playlist->public = true;
    } else {
        $this->view->vars->playlist->public = false;
    }
    
    // Create playlist if no errors were found
    if (!empty($this->view->vars->playlist->name) && isset($this->view->vars->playlist->public)) {
        $playlistMapper->save($this->view->vars->playlist);
        $this->view->vars->message = Language::GetText('success_playlist_updated');
        $this->view->vars->message_type = 'success';
    }
}

// Handle remove video from playlist if submitted
if (!empty($_GET['remove']) && is_numeric ($_GET['remove']) && $_GET['remove'] > 0) {
    $video = $videoMapper->getVideoById($_GET['remove']);
    if ($video && $playlistService->checkListing($video, $this->view->vars->playlist)) {
        $this->view->vars->playlist = $playlistService->deleteVideo($video, $this->view->vars->playlist);
        $this->view->vars->message = Language::GetText('success_playlist_video_removed');
        $this->view->vars->message_type = 'success';
    }
}

// Prepare page for render
$this->view->vars->meta->title = Functions::Replace($this->view->vars->meta->title, array ('playlist_name' => $playlistService->getPlaylistName($this->view->vars->playlist)));
$this->view->vars->videoList = $playlistService->getPlaylistVideos($this->view->vars->playlist);