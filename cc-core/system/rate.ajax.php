<?php

### Created on March 15, 2009
### Created by Miguel A. Hurtado
### This script performs all the user actions for a video via AJAX


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');
App::LoadClass ('Rating');
App::LoadClass ('Privacy');
App::LoadClass ('EmailTemplate');


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


// Verify rating was given
if (!isset ($_POST['rating']) || ($_POST['rating'] != 'Helpful' && $_POST['rating'] != 'Not Helpful')) {
    App::Throw404();
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

?>