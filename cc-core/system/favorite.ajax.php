<?php

### Created on March 15, 2009
### Created by Miguel A. Hurtado
### This script performs all the user actions for a video via AJAX


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');
App::LoadClass ('Favorite');


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

?>