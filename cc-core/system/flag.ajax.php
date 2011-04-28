<?php

//echo print_r ($_POST,true);
//exit();

### Created on March 15, 2009
### Created by Miguel A. Hurtado
### This script performs all the user actions for a video via AJAX


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');
App::LoadClass ('Flag');
App::LoadClass ('Comment');


// Establish page variables, objects, arrays, etc
$logged_in = User::LoginCheck();
if ($logged_in) $user = new User ($logged_in);



// Verify valid ID was provided
if (empty ($_POST['id']) || !is_numeric ($_POST['id']))  App::Throw404();
if (empty ($_POST['type']))  App::Throw404();



### Handle flag according to type of content being flagged
switch ($_POST['type']) {

    ### Handle report abuse video
    case 'video':

        // Verify a valid video was provided
        $video = new Video ($_POST['id']);
        if (!$video->found || $video->status != 6)  App::Throw404();


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
            echo json_encode (array ('result' => 1, 'msg' => (string) Language::GetText('success_flag')));
            exit();
        } else {
            echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText('error_flag_duplicate')));
            exit();
        }




    ### Handle report abuse comment
    case 'comment':

        // Check if user is logged in
        if (!$logged_in) {
            echo json_encode (array ('result' => 0, 'msg' => 'You must be logged in to report inappropriate comments!'));
            exit();
        }


        // Verify comment id was given
        if (!isset ($_POST['id']) || !is_numeric ($_POST['id'])) {
            exit();
        }
        $comment_id = trim ($_POST['id']);


        // Check if comment id is valid
        $comment = new Comment ($comment_id);
        if (!$comment->found) {
            exit();
        } elseif ($comment->user_id == $user->user_id) {
            echo json_encode (array ('result' => 0, 'msg' => 'You can\'t report your own comments!'));
            exit();
        }


        // Create Flag if one doesn't exist
        $data = array ('flag_type' => 'video-comment', 'id' => $comment_id, 'user_id' => $user->user_id);
        if (!Flag::Exist ($data)) {
            Flag::Create ($data);
            echo json_encode (array ('result' => 1, 'msg' => 'Thank you for reporting this comment. We will look into it immediately.'));
            exit();
        } else {
            echo json_encode (array ('result' => 0, 'msg' => 'You have already reported this comment! Thank you for your assistance.'));
            exit();
        }



    }   // END action switch

?>