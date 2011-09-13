<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');
App::LoadClass ('Flag');
App::LoadClass ('Comment');
Plugin::Trigger ('flag.ajax.start');


// Establish page variables, objects, arrays, etc
$logged_in = User::LoginCheck();
if ($logged_in) $user = new User ($logged_in);
Plugin::Trigger ('flag.ajax.login_check');


// Verify valid ID was provided
if (empty ($_POST['id']) || !is_numeric ($_POST['id']))  App::Throw404();
if (empty ($_POST['type']) || !in_array ($_POST['type'], array ('video', 'user', 'comment')))  App::Throw404();


try {

     // Check if user is logged in
    if (!$logged_in) throw new Exception (Language::GetText ('error_flag_login'));


    switch ($_POST['type']) {

        case 'video':
            $id = Video::Exist (array ('video_id' => $_POST['id'], 'status' => 'approved'));
            if (!$id) App::Throw404();
            $video = new Video ($_POST['id']);
            $member_id = $video->user_id;
            $url = $video->url;
            $name = "Title: $video->title";
            $type = 'Video';
            Plugin::Trigger ('flag.ajax.flag_video');
            break;

        case 'user':
            $id = User::Exist (array ('user_id' => $_POST['id'], 'status' => 'active'));
            if (!$id) App::Throw404();
            $member = new User ($id);
            $member_id = $id;
            $url = HOST . "/members/$member->username/";
            $name = "Username: $user->username";
            $type = 'Member';
            Plugin::Trigger ('flag.ajax.flag_user');
            break;

        case 'comment':
            $id = Comment::Exist (array ('comment_id' => $_POST['id'], 'status' => 'approved'));
            if (!$id) App::Throw404();
            $comment = new Comment ($_POST['id']);
            $member_id = $comment->user_id;
            $video = new Video ($comment->video_id);
            $url = $video->url;
            $name = "Comments: $comment->comments";
            $type = 'Comment';
            Plugin::Trigger ('flag.ajax.flag_comment');
            break;
            
    }


    // Verify user doesn't flag thier content
    if ($user->user_id == $member_id) throw new Exception (Language::GetText ('error_flag_own'));


    // Verify Flag doesn't exist
    $data = array ('type' => $_POST['type'], 'id' => $_POST['id'], 'user_id' => $user->user_id);
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

?>