<?php

### Created on March 15, 2009
### Created by Miguel A. Hurtado
### This script performs all the user actions for a video via AJAX


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('Video');
App::LoadClass ('Rating');
App::LoadClass ('Subscription');
App::LoadClass ('Flag');
App::LoadClass ('Favorite');
App::LoadClass ('Comment');
App::LoadClass ('Privacy');
App::LoadClass ('EmailTemplate');


// Establish page variables, objects, arrays, etc
$logged_in = User::LoginCheck();
$subscribed = NULL;
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



// Retrieve user data if logged in
if ($logged_in) {
    $user = new User ($logged_in);
    $data = array ('member' => $video->user_id, 'user_id' => $user->user_id);
    $id = Subscription::Exist ($data);
    $subscribed = ($id) ? $id : FALSE;
} else {
    $subscribed = FALSE;
}





/***********************
Handle page if submitted
***********************/

if (isset ($_POST['action'])) {
	
    switch ($_POST['action']) {
		
        ### Handle Rate Video if submitted
        case 'rate':

            // Verify rating was given
            if (!isset ($_POST['rating']) || ($_POST['rating'] != 'Helpful' && $_POST['rating'] != 'Not Helpful')) {
                break;
            }

            // Check user is logged in
            if (!$logged_in) {
                $user_id = User::IsAnonymous() ? $_COOKIE['tv_anonymous'] : User::CreateAnonymous();
            } else {
                $user_id = $user->user_id;
            }

            // Check user doesn't rate his own video
            if ($logged_in && $user->user_id == $video->user_id) {
                echo json_encode (array ('result' => 0, 'msg' => 'You can\'t rate your own videos'));
                exit();
            }

            // Submit vote if none exists
            $rating = new Rating ($video->video_id);
            if ($rating->AddVote($user_id, $_POST['rating'])) {
                echo json_encode (array ('result' => 1, 'msg' => 'Thank you! Your vote has been submitted.', 'other' => $rating->GetCountText()));
                exit();
            } else {
                echo json_encode (array ('result' => 0, 'msg' => 'You have already submitted your vote for this video!'));
                exit();
            }




        ### Handle comment video if submitted
        case 'comment':
			
            if (isset ($_POST['submitted'])) {
                
                // Verify user is logged in
                if (!$logged_in) {
                    echo json_encode (array ('result' => 0, 'msg' => 'You must login to comment on videos!'));
                    exit();
                }

                $video_comment = htmlspecialchars ($_POST['comments']);
                
                // Validate and submit comment
                if (Comment::Validate ($video_comment)) {
                    $data = array ('user_id' => $user->user_id, 'video_id' => $video->video_id, 'comments' => $video_comment, 'status' => 1);
                    Comment::Create ($data);

                    // Send video owner notifition if opted-in
                    $privacy = new Privacy ($video->user_id);
                    if ($privacy->OptCheck ('video_comment')) {
                        $template = new EmailTemplate ('/video_comment.htm');
                        $template_user = new User ($video->user_id);
                        $template_data = array (
                            'host'   => HOST,
                            'email'  => $template_user->email,
                            'title'  => $video->title
                        );
                        $template->Replace ($template_data);
                        $template->Send ($template_user->email);
                    }


                    ### Retrieve video comments
                    $query = "SELECT comment_id FROM video_comments WHERE video_id = $video->video_id AND status = 1 ORDER BY comment_id DESC LIMIT 0, 5";
                    $result_comments = $db->Query ($query);
                    

                    // Retrieve formatted new comment block
                    View::InitView();
                    ob_start();
                    View::RepeatingBlock('comment.tpl', $result_comments);
                    $comment_block = ob_get_contents();
                    ob_end_clean();
 

                    echo json_encode (array ('result' => 1, 'msg' => 'Your comment has successfully been submitted!', 'other' => $comment_block));
                    exit();

                } else {
                    echo json_encode (array ('result' => 0, 'msg' => 'Please enter your comments in the text box and try again.'));
                    exit();
                }
                
            }	
						



        ### Handle favorite video if submitted
        case 'favorite':

            // Verify user is logged in
            if (!$logged_in) {
                echo json_encode (array ('result' => 0, 'msg' => 'You must login to add this video to your favorites!'));
                exit();
            }
            
            // Check user doesn't fav. his own video
            if ($user->user_id == $video->user_id) {
                echo json_encode (array ('result' => 0, 'msg' => 'You can\'t add your own video to your favorites.'));
                exit();
            }
            
            // Create Favorite record if none exists
            $data = array ('user_id' => $user->user_id, 'video_id' => $video->video_id);
            if (!Favorite::Exist ($data)) {
                Favorite::Create ($data);
                echo json_encode (array ('result' => 1, 'msg' => 'You have successfully added this video to your favorites!'));
                exit();
            } else {
                echo json_encode (array ('result' => 0, 'msg' => 'This video is already in your favorites!'));
                exit();
            }
			



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




        ### Handle subscribe user to a member
        case 'subscribe':
            
            // Verify user is logged in
            if (!$logged_in) {
                echo json_encode (array ('result' => 0, 'msg' => 'You must be logged in to subscribe to members!'));
                exit();
            }

            // Check if user is subscribing to himself
            if ($user->user_id == $video->user_id) {
                echo json_encode (array ('result' => 0, 'msg' => 'You can\'t subscribe to yourself!'));
                exit();
            }

            // Create subscription record if none exists
            $data = array ('member' => $video->user_id, 'user_id' => $user->user_id);
            if (!Subscription::Exist ($data)) {
                $subscribed = Subscription::Create ($data);
                echo json_encode (array ('result' => 1, 'msg' => 'You have subscribed to ' . $video->username . '!'));
                exit();
            } else {
                echo json_encode (array ('result' => 0, 'msg' => 'You\'re already subscribed to this member!'));
                exit();
            }




        ### Handle unsubscribe user from a member
        case 'unsubscribe':
		
            // Verify user is logged in
            if (!$logged_in) {
                echo json_encode (array ('result' => 0, 'msg' => 'You must be logged in to subscribe to members!'));
                exit();
            }

            // Delete subscription if one exists
            if ($subscribed) {
                Subscription::Delete ($subscribed);
                echo json_encode (array ('result' => 1, 'msg' => 'You have unsubscribed from ' . $video->username . '!'));
                exit();
            } else {
                echo json_encode (array ('result' => 0, 'msg' => 'You\'re not subscribed to this member!'));
                exit();
            }		
			
			
        }   // END action switch
	
	
}   // END verify if page was submitted

?>