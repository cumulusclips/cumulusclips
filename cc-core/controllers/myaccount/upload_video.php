<?php

### Created on May 11, 2009
### Created by Miguel A. Hurtado
### This script allows users to actually browse for and upload their videos


// Include required files
include ('../../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');


// Establish page variables, objects, arrays, etc
View::InitView ('upload_video');
Plugin::Trigger ('upload_video.start');
View::$vars->logged_in = User::LoginCheck (HOST . '/login/');
View::$vars->user = new User (View::$vars->logged_in);



### Verify user entered video information
if (isset ($_SESSION['token'])) {
    $query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE MD5(CONCAT(video_id,'" . SECRET_KEY . "')) = '" . $db->Escape ($_SESSION['token']) . "' AND status IN (1, 2)";
    $result = $db->Query ($query);
    if ($db->Count ($result) == 1) {
        $row = $db->FetchObj ($result);
        $video = new Video ($row->video_id);
        $video->Update (array ('status' => 2));
    } else {
        header ('Location: ' . HOST . '/myaccount/upload/');
        exit();
    }

} else {
    header ('Location: ' . HOST . '/myaccount/upload/');
    exit();
}


// Output page
Plugin::Trigger ('upload_video.before_render');
View::Render ('myaccount/upload_video.tpl');

?>