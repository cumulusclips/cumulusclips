<?php

### Created on March 15, 2009
### Created by Miguel A. Hurtado
### This script performs all the user actions for a video via AJAX


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');
App::LoadClass ('Rating');
Plugin::Trigger ('rate.ajax.start');


// Establish page variables, objects, arrays, etc
$logged_in = User::LoginCheck();
if ($logged_in) $user = new User ($logged_in);
Plugin::Trigger ('rate.ajax.login_check');



// Verify a video was selected
if (!empty ($_POST['video_id']) && is_numeric ($_POST['video_id'])) {
    $video = new Video ($_POST['video_id']);
} else {
    App::Throw404();
}


// Check if video is valid
if (!$video->found || $video->status != 6) {
    App::Throw404();
}


// Verify rating was given
if (!isset ($_POST['rating']) || !in_array ($_POST['rating'], array ('1','0'))) {
    App::Throw404();
}


// Verify user is logged in
if (!$logged_in) {
    echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText('error_rate_login')));
    exit();
}


// Check user doesn't rate his own video
if ($logged_in && $user->user_id == $video->user_id) {
    echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText ('error_rate_own')));
    exit();
}


// Submit rating if none exists
if (Rating::AddRating ($_POST['rating'], $video->video_id, $user_id)) {
    Plugin::Trigger ('rate.ajax.rate_video');
    echo json_encode (array ('result' => 1, 'msg' => (string) Language::GetText ('success_rated'), 'other' => Rating::GetRating ($video->video_id)));
    exit();
} else {
    Plugin::Trigger ('rate.ajax.rate_video_duplicate');
    echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText ('error_rate_duplicate')));
    exit();
}

?>