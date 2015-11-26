<?php

// Verify if user is logged in
$userService = new UserService();
$this->view->vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf($this->view->vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$playlistMapper = new PlaylistMapper();
$playlistService = new PlaylistService();
$this->view->vars->message = null;

// Handle remove playlist if submitted
if (!empty($_GET['remove']) && is_numeric ($_GET['remove']) && $_GET['remove'] > 0) {
    $playlist = $playlistMapper->getPlaylistByCustom(array('playlist_id' => $_GET['remove'], 'user_id' => $this->view->vars->loggedInUser->userId));
    if ($playlist && !in_array($playlist->type, array('favorites', 'watch_later'))) {
        $playlistService->delete($playlist);
        $this->view->vars->message = Language::GetText('success_playlist_deleted');
        $this->view->vars->message_type = 'success';
    }
}

// Handle create new playlist if submitted
if (!empty($_POST['submitted'])) {
    $playlist = new Playlist();
    $playlist->userId = $this->view->vars->loggedInUser->userId;
    
    // Validate playlist name
    if (!empty($_POST['name'])) {
        $playlist->name = trim($_POST['name']);
    } else {
        $this->view->vars->message = Language::GetText('error_playlist_name');
        $this->view->vars->message_type = 'errors';
    }
    
    // Validate playlist visibility
    if (!empty($_POST['visibility']) && $_POST['visibility'] == 'public') {
        $playlist->public = true;
    } else {
        $playlist->public = false;
    }
    
    // Create playlist if no errors were found
    if (!empty($playlist->name) && isset($playlist->public)) {
        $playlistMapper->save($playlist);
        $this->view->vars->message = Language::GetText('success_playlist_created');
        $this->view->vars->message_type = 'success';
    }
}

// Retrieve user's playlists
$userLists = $playlistMapper->getUserPlaylists($this->view->vars->loggedInUser->userId);
$this->view->vars->userPlaylists = array();
foreach ($userLists as $playlist) {
    switch ($playlist->type)
    {
        case 'playlist':
            $this->view->vars->userPlaylists[] = $playlist;
            break;
        case 'favorites':
            $this->view->vars->favoritesList = $playlist;
            break;
        case 'watch_later':
            $this->view->vars->watchLaterList = $playlist;
            break;
    }
}