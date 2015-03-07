<?php

Plugin::triggerEvent('mobile_play.start');
Functions::redirectIf((boolean) Settings::get('mobile_site'), HOST . '/');

// Verify if user is logged in
$userService = new UserService();
$this->view->vars->loggedInUser = $userService->loginCheck();

// Establish page variables, objects, arrays, etc
$db = Registry::get('db');
$videoMapper = new VideoMapper();
$commentMapper = new CommentMapper();
$commentService = new CommentService();
$this->view->vars->webmEncodingEnabled = (Settings::get('webm_encoding_enabled') == '1') ? true : false;
$this->view->vars->theoraEncodingEnabled = (Settings::get('theora_encoding_enabled') == '1') ? true : false;

// Verify a video was selected
if (empty($_GET['vid']) || !is_numeric($_GET['vid']) || $_GET['vid'] < 1) App::Throw404();

// Verify video exists
$video = $videoMapper->getVideoByCustom(array(
    'video_id' => $_GET['vid'],
    'status' => 'approved',
    'private' => '0',
    'gated' => '0'
));
if (!$video) App::throw404();

// Retrieve video
$this->view->vars->video = $video;
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