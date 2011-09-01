<?php

### Created on March 15, 2009
### Created by Miguel A. Hurtado
### This script performs all the user actions for a video via AJAX


// Include required files
include ('../config/bootstrap.php');
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
if (empty ($_POST['type']) || !in_array ($_POST['type'], array ('video', 'member', 'comment')))  App::Throw404();



### Handle flag according to type of content being flagged
try {

     // Check if user is logged in
    if (!$logged_in) throw new Exception (Language::GetText ('error_flag_login'));



    switch ($_POST['type']) {
        case 'video':
            $id = Video::Exist (array ('video_id' => $_POST['id'], 'status' => 'approved'));
            if (!$id) App::Throw404();
            $video = new Video ($_POST['id']);
            $member_id = $video->user_id;
            break;
        case 'user':
            if (User::Exist (array ('user_id' => $_POST['id'], 'status' => 'active'))) App::Throw404();
            $member_id = $video->user_id;
            break;
        case 'comment':
            $id = Comment::Exist (array ('comment_id' => $_POST['id'], 'status' => 'approved'));
            if (!$id) App::Throw404();
            $comment = new Comment ($_POST['id']);
            $member_id = $comment->user_id;
            break;
    }



    // Verify user doesn't flag thier content
    if ($user->user_id == $member_id) throw new Exception (Language::GetText ('error_flag_own'));



    // Create Flag if one doesn't exist
    $data = array ('type' => $_POST['type'], 'id' => $_POST['id'], 'user_id' => $user->user_id);
    if (!Flag::Exist ($data)) {
        Flag::Create ($data);
        Plugin::Trigger ('flag.ajax.flag_video');
        echo json_encode (array ('result' => 1, 'msg' => (string) Language::GetText('success_flag')));
        exit();
    } else {
        throw new Exception (Language::GetText ('error_flag_duplicate'));
    }


    ### ADD ADMIN ALERT
    ### UPDATE PLUGIN HOOKS



} catch (Exception $e) {
    echo json_encode (array ('result' => 0, 'msg' => $e->getMessage()));
    exit();
}

?>