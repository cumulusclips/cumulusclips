<?php

Plugin::triggerEvent('mobile_play.start');
Functions::redirectIf((boolean) Settings::get('mobile_site'), HOST . '/');

// Verify if user is logged in
$this->authService->enforceTimeout();
$this->view->vars->loggedInUser = $this->authService->getAuthUser();

// Establish page variables, objects, arrays, etc
$db = Registry::get('db');
$videoMapper = new VideoMapper();
$commentMapper = new CommentMapper();
$commentService = new CommentService();

// Validate requested video
if (!empty($_GET['vid']) && $video = $videoMapper->getVideoByCustom(array('video_id' => $_GET['vid'], 'status' => 'approved'))) {

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
} else {
    App::throw404();
}

// Retrieve video
$this->view->vars->meta->title = $video->title;

// Retrieve comments
$this->view->vars->commentCount = $commentMapper->getVideoCommentCount($video->videoId);
$this->view->vars->commentCardList = $commentService->getVideoComments($video, 5);

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

Plugin::triggerEvent('mobile_play.end');