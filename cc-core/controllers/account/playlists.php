<?php

Plugin::triggerEvent('playlists.start');

// Verify if user is logged in
$this->authService->enforceAuth();
$this->authService->enforceTimeout(true);
$this->view->vars->loggedInUser = $this->authService->getAuthUser();

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

    // Validate form nonce token and submission speed
    if (
        !empty($_POST['nonce'])
        && !empty($_SESSION['formNonce'])
        && !empty($_SESSION['formTime'])
        && $_POST['nonce'] == $_SESSION['formNonce']
        && time() - $_SESSION['formTime'] >= 2
    ) {

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

    } else {
        $this->view->vars->message = Language::getText('invalid_session');
        $this->view->vars->message_type = 'errors';
    }
}

// Retrieve user's playlists
$userLists = $playlistMapper->getUserPlaylists($this->view->vars->loggedInUser->userId);
$this->view->vars->userPlaylists = array();
foreach ($userLists as $playlist) {
    switch ($playlist->type)
    {
        case \PlaylistMapper::TYPE_PLAYLIST:
            $this->view->vars->userPlaylists[] = $playlist;
            break;
        case \PlaylistMapper::TYPE_FAVORITES:
            $this->view->vars->favoritesList = $playlist;
            break;
        case \PlaylistMapper::TYPE_WATCH_LATER:
            $this->view->vars->watchLaterList = $playlist;
            break;
    }
}

// Generate new form nonce
$this->view->vars->formNonce = md5(uniqid(rand(), true));
$_SESSION['formNonce'] = $this->view->vars->formNonce;
$_SESSION['formTime'] = time();

Plugin::triggerEvent('playlists.end');

