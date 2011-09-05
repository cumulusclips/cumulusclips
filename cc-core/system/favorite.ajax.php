<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');
App::LoadClass ('Favorite');
Plugin::Trigger ('favorite.ajax.start');


// Establish page variables, objects, arrays, etc
$logged_in = User::LoginCheck();
if ($logged_in) $user = new User ($logged_in);
Plugin::Trigger ('favorite.ajax.login_check');



// Verify a valid video was provided
if (empty ($_POST['video_id']) || !is_numeric ($_POST['video_id'])) App::Throw404();
if (!Video::Exist (array ('video_id' => $_POST['video_id'], 'status' => 'approved'))) App::Throw404();
$video = new Video ($_POST['video_id']);


// Verify user is logged in
if (!$logged_in) {
    echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText('error_favorite_login')));
    exit();
}


// Check user doesn't fav. his own video
if ($user->user_id == $video->user_id) {
    echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText('error_favorite_own')));
    exit();
}


// Create Favorite record if none exists
$data = array ('user_id' => $user->user_id, 'video_id' => $video->video_id);
if (!Favorite::Exist ($data)) {
    Favorite::Create ($data);
    Plugin::Trigger ('favorite.ajax.favorite_video');
    echo json_encode (array ('result' => 1, 'msg' => (string) Language::GetText('success_favorite_added')));
    exit();
} else {
    echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText('error_favorite_duplicate')));
    exit();
}

?>