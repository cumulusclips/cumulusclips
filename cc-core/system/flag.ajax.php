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
switch ($_POST['type']) {

    ### Handle report abuse video
    case 'video':

        // Verify a valid video was provided
        $video = new Video ($_POST['id']);
        if (!$video->found || $video->status != 'approved')  App::Throw404();


        // Check if user is logged in
        if (!$logged_in) {
            echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText('error_flag_login')));
            exit();
        }


        // Verify user doesn't flag own content
        if ($user->user_id == $video->user_id) {
            echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText('error_flag_own')));
            exit();
        }


        // Create Flag if one doesn't exist
        $data = array ('type' => 'video', 'id' => $video->video_id, 'user_id' => $user->user_id);
        if (!Flag::Exist ($data)) {
            Flag::Create ($data);
            Plugin::Trigger ('flag.ajax.flag_video');
            echo json_encode (array ('result' => 1, 'msg' => (string) Language::GetText('success_flag')));
            exit();
        } else {
            echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText('error_flag_duplicate')));
            exit();
        }




    ### Handle report abuse user
    case 'member':

        // Verify a valid video was provided
        $member = new User ($_POST['id']);
        if (!$member->found || $member->status != 'active')  App::Throw404();


        // Check if user is logged in
        if (!$logged_in) {
            echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText('error_flag_login')));
            exit();
        }


        // Verify user doesn't flag himself
        if ($user->user_id == $member->user_id) {
            echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText('error_flag_own')));
            exit();
        }


        // Create Flag if one doesn't exist
        $data = array ('type' => 'member', 'id' => $member->user_id, 'user_id' => $user->user_id);
        if (!Flag::Exist ($data)) {
            Flag::Create ($data);
            Plugin::Trigger ('flag.ajax.flag_member');
            echo json_encode (array ('result' => 1, 'msg' => (string) Language::GetText('success_flag')));
            exit();
        } else {
            echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText('error_flag_duplicate')));
            exit();
        }




    ### Handle report abuse comment
    case 'comment':

        // Verify a valid comment was provided
        $comment = new Comment ($_POST['id']);
        if (!$comment->found || $comment->status != 'approved')  App::Throw404();


        // Check if user is logged in
        if (!$logged_in) {
            echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText('error_flag_login')));
            exit();
        }


        // Verify user doesn't flag thier comment
        if ($user->user_id == $comment->user_id) {
            echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText('error_flag_own')));
            exit();
        }


        // Create Flag if one doesn't exist
        $data = array ('type' => 'comment', 'id' => $comment->comment_id, 'user_id' => $user->user_id);
        if (!Flag::Exist ($data)) {
            Flag::Create ($data);
            Plugin::Trigger ('flag.ajax.flag_comment');
            echo json_encode (array ('result' => 1, 'msg' => (string) Language::GetText('success_flag')));
            exit();
        } else {
            echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText('error_flag_duplicate')));
            exit();
        }

    }   // END action switch

?>