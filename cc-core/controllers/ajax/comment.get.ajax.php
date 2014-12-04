<?php

// Establish page variables, objects, arrays, etc
$this->view->options->disableView = true;
$videoMapper = new VideoMapper();
$commentService = new CommentService();
$limit = 5;
$lastCommentId = 0;

// Verify a video was selected
if (!empty($_GET['videoId'])) {
    $video = $videoMapper->getVideoById($_GET['videoId']);
} else {
    App::Throw404();
}

// Check if video is valid
if (!$video || $video->status != 'approved' || $video->commentsClosed) {
    App::Throw404();
}

// Validate comment limit
if (!empty($_GET['limit']) && is_numeric($_GET['limit']) && $_GET['limit'] > 0) {
    $limit = $_GET['limit'];
}

// Validate comment id offset
if (!empty($_GET['lastCommentId']) && is_numeric($_GET['lastCommentId']) && $_GET['lastCommentId'] > 0) {
    $lastCommentId = $_GET['lastCommentId'];
}

$commentCardList = $commentService->getVideoComments($video, $limit, $lastCommentId);
echo json_encode(array('result' => 1, 'message' => '', 'other' => array('commentCardList' => $commentCardList)));
exit();
