<?php

// Verify if user is logged in
$loggedInUser = $this->authService->getAuthUser();

// Establish page variables, objects, arrays, etc
$this->view->options->disableView = true;
$errors = array();
$videoMapper = new VideoMapper();
$commentMapper = new CommentMapper();
$comment = new Comment();

// Verify a video was selected
if (!empty($_POST['videoId'])) {
    $video = $videoMapper->getVideoById($_POST['videoId']);

    // Check if video is valid
    if (!$video || $video->status != 'approved' || $video->commentsClosed) App::Throw404();

    $comment->videoId = $video->videoId;
} else {
    App::Throw404();
}

// Verify user is logged in
if ($loggedInUser) {
    $comment->userId = $loggedInUser->userId;
} else {
    echo json_encode(array('result' => 0, 'message' => (string) Language::GetText('error_comment_login')));
    exit();
}

// Validate parent comment
if (!empty($_POST['parentCommentId'])) {
    $comment->parentId = trim($_POST['parentCommentId']);
}

// Validate comments
if (!empty($_POST['comments'])) {
    $comment->comments = trim($_POST['comments']);
} else {
    $errors['comments'] = Language::GetText('error_comment');
}

// Save comment if no errors were found
if (empty($errors)) {
    $comment->status = 'new';
    $commentId = $commentMapper->save($comment);
    $newComment = $commentMapper->getCommentById($commentId);
    $commentService = new CommentService();
    $commentService->approve($newComment, 'create');
    $newCommentCard = $commentService->getCommentCard($newComment);

    // Retrieve formatted new comment
    if (Settings::Get('auto_approve_comments') == 1) {
        $message = (string) Language::GetText('success_comment_posted');
        $other = array(
            'autoApprove' => true,
            'commentCard' => $newCommentCard,
            'commentCount' => $commentMapper->getVideoCommentCount($newComment->videoId)
        );
    } else {
        $message = (string) Language::GetText('success_comment_approve');
        $other = array('autoApprove' => false);
    }
    echo json_encode(array('result' => true, 'message' => $message, 'other' => $other));
    exit();
} else {
    $error_msg = Language::GetText('errors_below');
    $error_msg .= '<br /><br /> - ' . implode('<br /> - ', $errors);
    echo json_encode(array('result' => false, 'message' => $error_msg));
    exit();
}