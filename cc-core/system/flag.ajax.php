<?php

// Init view
Plugin::triggerEvent('flag.ajax.start');

// Verify if user is logged in
$userService = new UserService();
$loggedInUser = $userService->loginCheck();
Functions::RedirectIf($view->vars->loggedInUser, HOST . '/login/');
Plugin::Trigger ('flag.ajax.login_check');

// Verify valid ID was provided
if (empty ($_POST['id']) || !is_numeric ($_POST['id']))  App::Throw404();
if (empty ($_POST['type']) || !in_array ($_POST['type'], array ('video', 'member', 'comment')))  App::Throw404();

try {
     // Check if user is logged in
    if (!$loggedInUser) throw new Exception (Language::GetText ('error_flag_login'));

    switch ($_POST['type']) {
        case 'video':
            $videoMapper = new VideoMapper();
            $video = $videoMapper->getVideoByCustom(array('video_id' => $_POST['id'], 'status' => 'approved'));
            if (!$video) App::Throw404();
            $member_id = $video->userId;
            $url = $video->url;
            $name = "Title: $video->title";
            $type = 'Video';
            Plugin::Trigger ('flag.ajax.flag_video');
            break;
        case 'member':
            $userMapper = new UserMapper();
            $member = $userMapper->getUserByCustom(array('user_id' => $_POST['id'], 'status' => 'active'));
            if (!$member) App::Throw404();
            $member_id = $member->userId;
            $url = HOST . "/members/$member->username/";
            $name = "Username: $user->username";
            $type = 'Member';
            Plugin::Trigger ('flag.ajax.flag_user');
            break;
        case 'comment':
            $commentMapper = new CommentMapper();
            $comment = $commentMapper->getCommentByCustom(array('comment_id' => $_POST['id'], 'status' => 'approved'));
            if (!$comment) App::Throw404();
            $member_id = $comment->userId;
            $video = new Video ($comment->videoId);
            $url = $video->url;
            $name = "Comments: $comment->comments";
            $type = 'Comment';
            Plugin::Trigger ('flag.ajax.flag_comment');
            break;
    }

    // Verify user doesn't flag thier content
    if ($loggedInUser->userId == $member_id) throw new Exception (Language::GetText ('error_flag_own'));

    // Verify Flag doesn't exist
    $data = array ('type' => $_POST['type'], 'id' => $_POST['id'], 'user_id' => $loggedInUser->userId);
    if (Flag::Exist ($data)) throw new Exception (Language::GetText ('error_flag_duplicate'));
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
    Flag::Create ($data);
    Plugin::Trigger ('flag.ajax.flag');
    echo json_encode (array ('result' => 1, 'msg' => (string) Language::GetText('success_flag')));
    exit();
} catch (Exception $e) {
    echo json_encode (array ('result' => 0, 'msg' => $e->getMessage()));
    exit();
}