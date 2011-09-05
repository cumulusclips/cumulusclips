<?php

// Include required files
include_once (dirname (dirname (dirname (__FILE__))) . '/config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');


// Establish page variables, objects, arrays, etc
View::InitView ('upload_video');
Plugin::Trigger ('upload_video.start');
Functions::RedirectIf (View::$vars->logged_in = User::LoginCheck(), HOST . '/login/');
App::EnableUploadsCheck();
View::$vars->user = new User (View::$vars->logged_in);
View::$vars->timestamp = time();



### Verify user entered video information
if (isset ($_SESSION['upload'])) {

    if (Video::Exist (array ('video_id' => $_SESSION['upload'], 'status' => 'new'))) {
        $video = new Video ($_SESSION['upload']);
        $_SESSION['upload_key'] = md5 (md5 (View::$vars->timestamp) . SECRET_KEY);
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