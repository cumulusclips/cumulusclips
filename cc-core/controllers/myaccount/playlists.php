<?php

Plugin::triggerEvent('edit_video.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf($view->vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$playlistMapper = new PlaylistMapper();
$playlistService = new PlaylistService();
$view->vars->message = null;

// Handle remove playlist if submitted
if (!empty($_GET['remove']) && is_numeric ($_GET['remove']) && $_GET['remove'] > 0) {
    $playlist = $playlistMapper->getPlaylistByCustom(array('playlist_id' => $_GET['remove'], 'user_id' => $view->vars->loggedInUser->userId));
    if ($playlist && !in_array($playlist->type, array('favorites', 'watch_later'))) {
        $playlistService->delete($playlist);
        $view->vars->message = Language::GetText('success_playlist_deleted');
        $view->vars->message_type = 'success';
        Plugin::Trigger ('myfavorites.remove_favorite');
    }
}

// Handle create new playlist if submitted
if (!empty($_POST['submitted'])) {
    $playlist = new Playlist();
    $playlist->userId = $view->vars->loggedInUser->userId;
    
    // Validate playlist name
    if (!empty($_POST['name'])) {
        $playlist->name = trim($_POST['name']);
    } else {
        $view->vars->message = Language::GetText('error_playlist_name');
        $view->vars->message_type = 'errors';
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
        $view->vars->message = Language::GetText('success_playlist_created');
        $view->vars->message_type = 'success';
    }
}

// Retrieve user's playlists
$userLists = $playlistMapper->getUserPlaylists($view->vars->loggedInUser->userId);
$view->vars->userPlaylists = array();
foreach ($userLists as $playlist) {
    switch ($playlist->type)
    {
        case 'playlist':
            $view->vars->userPlaylists[] = $playlist;
            break;
        case 'favorites':
            $view->vars->favoritesList = $playlist;
            break;
        case 'watch_later':
            $view->vars->watchLaterList = $playlist;
            break;
    }
}

Plugin::Trigger ('myfavorites.before_render');