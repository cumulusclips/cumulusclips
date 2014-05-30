<?php

Plugin::triggerEvent('flag.ajax.start');

// Verify if user is logged in
$userService = new UserService();
$loggedInUser = $userService->loginCheck();

$view->disableView = true;
Plugin::Trigger ('flag.ajax.login_check');
$flagMapper = new FlagMapper();
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
            $videoMapper = new VideoMapper();
            $videoService = new VideoService();
            $video = $videoMapper->getVideoByCustom(array('video_id' => $_POST['id'], 'status' => 'approved'));
            if (!$video) App::Throw404();
            $contentOwnerUserId = $video->userId;
            $url = $videoService->getUrl($video);
            $name = "Title: $video->title";
            $type = 'Video';
            $flag->type = 'video';
            $flag->objectId = $video->videoId;
            Plugin::Trigger ('flag.ajax.flag_video');
            break;
        case 'user':
            $userMapper = new UserMapper();
            $user = $userMapper->getUserByCustom(array('user_id' => $_POST['id'], 'status' => 'active'));
            if (!$user) App::Throw404();
            $contentOwnerUserId = $user->userId;
            $url = HOST . "/members/$user->username/";
            $name = "Username: $user->username";
            $type = 'Member';
            $flag->type = 'user';
            $flag->objectId = $user->userId;
            Plugin::Trigger ('flag.ajax.flag_user');
            break;
        case 'comment':
            $commentMapper = new CommentMapper();
            $comment = $commentMapper->getCommentByCustom(array('comment_id' => $_POST['id'], 'status' => 'approved'));
            if (!$comment) App::Throw404();
            $contentOwnerUserId = $comment->userId;
            $videoService = new VideoService();
            $url = $videoService->getUrl($videoMapper->getVideoById($comment->videoId));
            $name = "Comments: $comment->comments";
            $type = 'Comment';
            $flag->type = 'comment';
            $flag->objectId = $comment->commentId;
            Plugin::Trigger ('flag.ajax.flag_comment');
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
    Plugin::Trigger ('flag.ajax.before_flag');

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
        Plugin::Trigger ('flag.ajax.alert');
        App::Alert ($subject, $body);
    }

    // Create flag and output message
    $flagMapper->save($flag);
    Plugin::Trigger ('flag.ajax.flag');
    echo json_encode (array ('result' => 1, 'message' => (string) Language::GetText('success_flag')));
    exit();
} catch (Exception $e) {
    echo json_encode (array ('result' => 0, 'message' => $e->getMessage()));
    exit();
}