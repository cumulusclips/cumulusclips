<?php

Plugin::triggerEvent('watch.start');

// @deprecated Deprecated in 2.5.0, removed in 2.6.0. Use watch.start instead
Plugin::triggerEvent('play.start');

// Verify if user is logged in
$this->authService->enforceTimeout();
$this->view->vars->loggedInUser = $this->authService->getAuthUser();

// Establish page variables, objects, arrays, etc
$db = Registry::get('db');
$userMapper = new UserMapper();
$videoMapper = new VideoMapper();
$commentMapper = new CommentMapper();
$playlistMapper = new PlaylistMapper();
$commentService = new CommentService();
$videoService = new VideoService();
$ratingService = new RatingService();
$playlistService = new \PlaylistService();
$fileService = new \FileService();
$this->view->vars->tags = null;
$this->view->vars->playlist = null;
$this->view->vars->playlistVideos = null;
$this->view->vars->webmEncodingEnabled = (Settings::get('webm_encoding_enabled') == '1') ? true : false;
$this->view->vars->theoraEncodingEnabled = (Settings::get('theora_encoding_enabled') == '1') ? true : false;

// Validate requested video
if (!empty($_GET['video_id']) && $video = $videoMapper->getVideoByCustom(array('video_id' => $_GET['video_id'], 'status' => 'approved'))) {

    $this->view->vars->video = $video;

    // Prevent public URL access to private video for all users except owner
    if (
        ($this->view->vars->video->private == '1' && !$this->view->vars->loggedInUser)
        || ($this->view->vars->video->private == '1' && $this->view->vars->loggedInUser->userId != $this->view->vars->video->userId)
    ) {
        App::throw404();
    }

} else if (!empty($_GET['private']) && $video = $videoMapper->getVideoByCustom(array('status' => 'approved', 'private_url' => $_GET['private']))) {
    $this->view->vars->video = $video;
} else if (!empty($_GET['get_private'])) {
    exit($videoService->generatePrivate());
} else {
    App::throw404();
}

// Load video data for page rendering
$this->view->vars->member = $userMapper->getUserById($this->view->vars->video->userId);
$this->view->vars->video->views++;
$videoMapper->save($this->view->vars->video);
$this->view->vars->rating = $ratingService->getRating($this->view->vars->video->videoId);
$this->view->vars->meta->title = $this->view->vars->video->title;
$this->view->vars->meta->keywords = implode (', ',$this->view->vars->video->tags);
$this->view->vars->meta->description = $this->view->vars->video->description;
$this->view->vars->attachments = $fileService->getVideoAttachments($video);

// Retrieve user data if logged in
if ($this->view->vars->loggedInUser) {
    $subscriptionService = new SubscriptionService();
    $this->view->vars->subscribe_text = ($subscriptionService->checkSubscription($this->view->vars->loggedInUser->userId, $this->view->vars->video->userId)) ? 'unsubscribe' : 'subscribe';
} else {
    $this->view->vars->subscribe_text = 'subscribe';
}

// Retrieve user's playlists
if ($this->view->vars->loggedInUser) {
    $userLists = $playlistMapper->getUserPlaylists($this->view->vars->loggedInUser->userId);
    $this->view->vars->userPlaylists = array();
    foreach ($userLists as $list) {
        switch ($list->type)
        {
            case 'playlist':
                $this->view->vars->userPlaylists[] = $list;
                break;
            case 'favorites':
                $this->view->vars->favoritesList = $list;
                $this->view->vars->favoritesListed = $playlistService->checkListing($video, $list);
                break;
            case 'watch_later':
                $this->view->vars->watchLaterList = $list;
                $this->view->vars->watchLaterListed = $playlistService->checkListing($video, $list);
                break;
        }
    }
}

// Load playlist videos if applicable
if (!empty($_GET['playlist'])) {
    $playlistService = new PlaylistService();
    $playlist = $playlistMapper->getPlaylistById($_GET['playlist']);
    if (
        $playlist
        && ($playlist->public || ($this->view->vars->loggedInUser && $this->view->vars->loggedInUser->userId == $playlist->userId))
        && $playlistService->checkListing($video, $playlist)
    ) {
        $this->view->vars->playlist = $playlist;
        $this->view->vars->playlistVideos = $playlistService->getPlaylistVideos($playlist);
    }
}

// Retrieve count of all videos
$query = "SELECT COUNT(video_id) as total FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0'";
$total = $db->fetchRow($query);

### Retrieve related videos
if ($total['total'] > 20) {
    // Use FULLTEXT query
    $search_terms = $this->view->vars->video->title . ' ' . implode (' ', $this->view->vars->video->tags);
    $query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE MATCH(title, tags, description) AGAINST (:searchTerms) AND status = 'approved' AND private = '0' AND video_id != :videoId LIMIT 9";
    $relatedVideosResults = $db->fetchAll($query, array(':searchTerms' => $search_terms, ':videoId' => $this->view->vars->video->videoId));
} else {
    // Use LIKE query
    $replacements = array(':videoId' => $this->view->vars->video->videoId);
    $tags = $this->view->vars->video->tags;
    foreach ($tags as $key => $tag) {
        $sub_queries[] = "video_id IN (SELECT video_id FROM " . DB_PREFIX . "videos WHERE title LIKE :tag$key OR description LIKE :tag$key OR tags LIKE :tag$key)";
        $replacements[':tag' . $key] = '%' . $tag . '%';
    }

    $sub_queries = implode(' OR ', $sub_queries);
    $query = 'SELECT video_id FROM ' . DB_PREFIX . 'videos WHERE (' . $sub_queries . ') AND status = "approved" AND private = "0" AND video_id != :videoId LIMIT 9';
    $relatedVideosResults = $db->fetchAll($query, $replacements);
}
$this->view->vars->relatedVideos = $videoMapper->getVideosFromList(
    Functions::arrayColumn($relatedVideosResults, 'video_id')
);

// Retrieve comments
$this->view->vars->commentCount = $commentMapper->getVideoCommentCount($video->videoId);
$this->view->vars->commentCardList = $commentService->getVideoComments($video, 5);

Plugin::triggerEvent('watch.end');

// @deprecated Deprecated in 2.5.0, removed in 2.6.0. Use watch.end instead
Plugin::triggerEvent('play.end');