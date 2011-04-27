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


// Establish page variables, objects, arrays, etc
$logged_in = User::LoginCheck();
if ($logged_in) $user = new User ($logged_in);
$subscribed = NULL;
$Errors = array();
$data = array();



// Verify a video was selected
if (isset ($_GET['vid']) && is_numeric ($_GET['vid'])) {
    $video = new Video ($_GET['vid']);
} else {
    App::Throw404();
}



// Check if video is valid
if (!$video->found || $video->status != 6) {
    App::Throw404();
}





/***********************
Handle page if submitted
***********************/

if (isset ($_POST['action'])) {
	
    switch ($_POST['action']) {



        ### Handle report abuse video
        case 'flag':

            // Verify flag was given
            if (!isset ($_POST['flag'])) {
                exit();
            }

            // Check if user is logged in
            if (!$logged_in) {
                echo json_encode (array ('result' => 0, 'msg' => 'You must be logged in to report inappropriate content!'));
                exit();
            }


            // Verify user doesn't flag own content
            if ($user->user_id == $video->user_id) {
                echo json_encode (array ('result' => 0, 'msg' => 'You can\'t report your own video!'));
                exit();
            }

			
            // Create Flag if one doesn't exist
            $data = array ('flag_type' => 'video', 'id' => $video->video_id, 'user_id' => $user->user_id);
            if (!Flag::Exist ($data)) {
                Flag::Create ($data);
                echo json_encode (array ('result' => 1, 'msg' => 'Thank you for reporting this video. We will look into this video immediately.'));
                exit();
            } else {
                echo json_encode (array ('result' => 0, 'msg' => 'You have already reported this video! Thank you for your assistance.'));
                exit();
            }
			



        ### Handle report abuse comment
        case 'flag-comment':

            // Check if user is logged in
            if (!$logged_in) {
                echo json_encode (array ('result' => 0, 'msg' => 'You must be logged in to report inappropriate comments!'));
                exit();
            }


            // Verify comment id was given
            if (!isset ($_POST['comment']) || !is_numeric ($_POST['comment'])) {
                exit();
            }
            $comment_id = trim ($_POST['comment']);


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
	
	
}   // END verify if page was submitted

?>