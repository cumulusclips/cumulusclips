<?php

### Created on May 11, 2009
### Created by Miguel A. Hurtado
### This script allows users to actually browse for and upload their videos


// Include required files
include ('../../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');
View::InitView();


// Establish page variables, objects, arrays, etc
View::LoadPage ('upload_video');
View::$vars->logged_in = User::LoginCheck (HOST . '/login/');
View::$vars->user = new User (View::$vars->logged_in);



### Verify user entered video information
if (isset ($_SESSION['token'])) {
    $query = "SELECT video_id FROM videos WHERE MD5(CONCAT(video_id,'" . SECRET_KEY . "')) = '" . $db->Escape ($_SESSION['token']) . "' AND status IN (1, 2)";
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
View::AddMeta ('uploadify:host', HOST);
View::AddMeta ('uploadify:token', $_SESSION['token']);
View::AddMeta ('uploadify:theme', THEME);
View::AddMeta ('uploadify:limit', VIDEO_SIZE_LIMIT);
View::AddCss ('uploadify.css');
View::AddJs ('swfobject.js');
View::AddJs ('uploadify.plugin.js');
View::AddJs ('uploadify.js');
View::SetLayout ('portal.layout.tpl');
View::Render ('myaccount/upload_video.tpl');

?>