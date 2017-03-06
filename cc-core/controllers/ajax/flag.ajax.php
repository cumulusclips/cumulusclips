<?php

// Verify if user is logged in
$loggedInUser = $this->authService->getAuthUser();

$this->view->options->disableView = true;
$flagMapper = new FlagMapper();
$videoMapper = new VideoMapper();
$userMapper = new UserMapper();
$commentMapper = new CommentMapper();
$videoService = new VideoService();
$flag = new Flag();

// Verify valid ID was provided
if (empty ($_POST['id']) || !is_numeric ($_POST['id']))  App::Throw404();
if (empty ($_POST['type']) || !in_array ($_POST['type'], array ('video', 'user', 'comment')))  App::Throw404();

try {
     // Check if user is logged in
    if (!$loggedInUser) throw new Exception (Language::GetText ('error_flag_login'));
    $flag->userId = $loggedInUser->userId;

    switch ($_POST['type']) {
        case 'video':
            $video = $videoMapper->getVideoByCustom(array('video_id' => $_POST['id'], 'status' => 'approved'));
            if (!$video) App::Throw404();
            $contentOwnerUserId = $video->userId;
            $url = $videoService->getUrl($video);
            $name = "Title: $video->title";
            $type = 'Video';
            $flag->type = 'video';
            $flag->objectId = $video->videoId;
            break;
        case 'user':
            $user = $userMapper->getUserByCustom(array('user_id' => $_POST['id'], 'status' => 'active'));
            if (!$user) App::Throw404();
            $contentOwnerUserId = $user->userId;
            $url = HOST . "/members/$user->username/";
            $name = "Username: $user->username";
            $type = 'Member';
            $flag->type = 'user';
            $flag->objectId = $user->userId;
            break;
        case 'comment':
            $comment = $commentMapper->getCommentByCustom(array('comment_id' => $_POST['id'], 'status' => 'approved'));
            if (!$comment) App::Throw404();
            $contentOwnerUserId = $comment->userId;
            $url = $videoService->getUrl($videoMapper->getVideoById($comment->videoId));
            $name = "Comments: $comment->comments";
            $type = 'Comment';
            $flag->type = 'comment';
            $flag->objectId = $comment->commentId;
            break;
    }

    // Verify user doesn't flag thier content
    if ($loggedInUser->userId == $contentOwnerUserId) throw new Exception (Language::GetText ('error_flag_own'));

    // Verify flag doesn't exist
    $flagExistCheck = $flagMapper->getFlagByCustom(array(
        'type' => $flag->type,
        'object_id' => $flag->objectId,
        'user_id' => $flag->userId
    ));
    if ($flagExistCheck) throw new Exception (Language::GetText ('error_flag_duplicate'));

    // Send admin alert
    if (Settings::Get ('alerts_flags') == '1') {
        $subject = 'Content Flagged As Inappropriate';
        $body = "One of your members flagged content as inappropriate. ";
        $body .= "Please review the content to verify it is valid. ";
        $body .= "You can login to the Admin Panel to dismiss the flag, or uphold it and ban the content.";

        $body .= "\n\n=======================================================\n";
        $body .= "Content Type: $type\n";
        $body .= "URL: $url\n";
        $body .= "$name\n";
        $body .= "=======================================================";
        App::Alert ($subject, $body);
    }

    // Create flag and output message
    $flagMapper->save($flag);
    echo json_encode (array ('result' => true, 'message' => (string) Language::GetText('success_flag')));
    exit();
} catch (Exception $e) {
    echo json_encode (array ('result' => false, 'message' => $e->getMessage()));
    exit();
}