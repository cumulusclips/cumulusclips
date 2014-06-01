<?php

Plugin::triggerEvent('comment.ajax.start');

// Verify if user is logged in
$userService = new UserService();
$loggedInUser = $userService->loginCheck();
Plugin::triggerEvent('comment.ajax.login_check');

// Establish page variables, objects, arrays, etc
$this->view->disableView = true;
$errors = array();
$data = array();
$videoMapper = new VideoMapper();
$commentMapper = new CommentMapper();

// Verify a video was selected
if (!empty($_POST['videoId'])) {
    $video = $videoMapper->getVideoById($_POST['videoId']);
} else {
    App::Throw404();
}

// Check if video is valid
if (!$video || $video->status != 'approved') {
    App::Throw404();
}

// Create comment if requested
if (isset($_POST['submitted'])) {
    
    $comment = new Comment();

    // Verify user is logged in
    if ($loggedInUser) {
        $comment->userId = $loggedInUser->userId;
    } else {
        $comment->userId = 0;
        $data['ip'] = $_SERVER['REMOTE_ADDR'];
        $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

        // Validate name
        if (!empty($_POST['name'])) {
            $comment->name = trim($_POST['name']);
        } else {
            $errors['name'] = Language::GetText('error_name');
        }

        // Validate email address
        $email_pattern = '/^[a-z0-9][a-z0-9_\.\-]+@[a-z0-9][a-z0-9\.\-]+\.[a-z0-9]{2,4}$/i';
        if (!empty($_POST['email']) && preg_match($email_pattern, $_POST['email'])) {
            $comment->email = trim($_POST['email']);
        } else {
            $errors['email'] = Language::GetText('error_email');
        }

        // Validate website
        $website_pattern = '/^(https?:\/\/)?[a-z0-9][a-z0-9\.\-]+\.[a-z0-9]{2,4}$/i';
        if (!empty($_POST['website']) && preg_match($website_pattern, $_POST['website'], $matches)) {
            $comment->website = (empty($matches[1]) ? 'http://' : '') . trim($_POST['website']);
        }
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

    // Validate output format block
    if (!empty($_POST['block'])) {
        $block = $_POST['block'] . '.tpl';
    } else {
        $block = null;
    }

    // Save comment if no errors were found
    if (empty($errors)) {
        $comment->videoId = $video->videoId;
        $comment->status = 'new';
        Plugin::triggerEvent('comment.ajax.before_post_comment');
        $commentId = $commentMapper->save($comment);
        $newComment = $commentMapper->getCommentById($commentId);
        $commentService = new CommentService();
        $commentService->approve($newComment, 'create');
        $newCommentCard = $commentService->getCommentCard($newComment);

        // Retrieve formatted new comment
        if (Settings::Get('auto_approve_comments') == 1) {
            $message = (string) Language::GetText('success_comment_posted');
            $other = array('autoApprove' => true, 'commentCard' => $newCommentCard);
        } else {
            $message = (string) Language::GetText('success_comment_approve');
            $other = array('autoApprove' => false);
        }
        echo json_encode(array('result' => true, 'message' => $message, 'other' => $other));
        Plugin::triggerEvent('comment.ajax.post_comment');
        exit();
    } else {
        $error_msg = Language::GetText('errors_below');
        $error_msg .= '<br /><br /> - ' . implode('<br /> - ', $errors);
        echo json_encode(array('result' => false, 'message' => $error_msg));
        exit();
    }
}