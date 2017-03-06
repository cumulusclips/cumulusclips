<?php

Plugin::triggerEvent('profile.start');

// Verify if user is logged in
$this->authService->enforceTimeout();
$this->view->vars->loggedInUser = $this->authService->getAuthUser();

// Establish page variables, objects, arrays, etc
$userService = new UserService();
$playlistService = new PlaylistService();

// Verify Member was supplied
$userMapper = new UserMapper();
if (!empty($_GET['username'])) {
    $profileUser = $userMapper->getUserByCustom(array('username' => $_GET['username'], 'status' => 'Active'));
} else {
    App::Throw404();
}

// Verify Member exists
if ($profileUser) {
    $this->view->vars->member = $profileUser;
    $this->view->vars->meta->title = Functions::Replace($this->view->vars->meta->title, array('member' => $profileUser->username));
} else {
    App::Throw404();
}

// Check if user is subscribed
if ($this->view->vars->loggedInUser) {
    $subscriptionService = new SubscriptionService();
    $this->view->vars->subscribe_text = $subscriptionService->checkSubscription($this->view->vars->loggedInUser->userId, $profileUser->userId) ? 'unsubscribe' : 'subscribe';
} else {
    $this->view->vars->subscribe_text = 'subscribe';
}

// Retrieve Logged in user's 'Watch Later' playlist
$this->view->vars->watchLaterPlaylistId = ($this->view->vars->loggedInUser) ? $playlistService->getUserSpecialPlaylist($this->view->vars->loggedInUser, \PlaylistMapper::TYPE_WATCH_LATER)->playlistId : '';

// Count subscription
$db = Registry::get('db');
$query = "SELECT COUNT(subscription_id) as count FROM " . DB_PREFIX . "subscriptions WHERE member = " . $profileUser->userId;
$countResult = $db->fetchRow($query);
$this->view->vars->sub_count = $countResult['count'];

// Update Member's profile view count
$this->view->vars->member->views++;
$userMapper->save($this->view->vars->member);

// Retrieve member's video & playlist counts
$videoCount = $userService->getVideoCount($profileUser);
$this->view->vars->videoCount = $videoCount;
$playlistCount = $userService->getPlaylistCount($profileUser);
$this->view->vars->playlistCount = $playlistCount;

// Retrieve member's video list
if ($videoCount > 0) {
    $videoMapper = new VideoMapper();
    $query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE user_id = " . $profileUser->userId . " AND status = 'approved' AND private = '0' ORDER BY date_created DESC LIMIT 9";
    $memberVideosResults = $db->fetchAll($query);
    $this->view->vars->videoList = $videoMapper->getVideosFromList(
        Functions::arrayColumn($memberVideosResults, 'video_id')
    );
}

// Retrieve user's playlists
if ($playlistCount > 0) {
    $playlistMapper = new PlaylistMapper();
    $query = "SELECT playlist_id FROM " . DB_PREFIX . "playlists WHERE user_id = " . $profileUser->userId . " AND public = 1 AND type = 'playlist' ORDER BY date_created DESC LIMIT 9";
    $memberPlaylistResults = $db->fetchAll($query);
    $this->view->vars->playlist_list = $playlistMapper->getPlaylistsFromList(
        Functions::arrayColumn($memberPlaylistResults, 'playlist_id')
    );
}

Plugin::triggerEvent('profile.end');