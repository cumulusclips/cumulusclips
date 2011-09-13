<?php

// Include required files
include_once (dirname (dirname (dirname (__FILE__))) . '/config/bootstrap.php');
App::LoadClass ('Video');


// Establish page variables, objects, arrays, etc
View::InitView ('mobile_videos');
Plugin::Trigger ('mobile_videos.start');


// Retrieve video count
$query = "SELECT COUNT(video_id) FROM " . DB_PREFIX . "videos WHERE status = 'approved'";
$result = $db->Query ($query);
View::$vars->count = $db->FetchRow ($result);
View::$vars->count = View::$vars->count[0];


// Retrieve video list
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' ORDER BY video_id DESC LIMIT 20";
View::$vars->videos = array();
$result = $db->Query ($query);
while ($video = $db->FetchObj ($result)) View::$vars->videos[] = $video->video_id;


// Output Page
Plugin::Trigger ('mobile_videos.before_render');
View::Render ('videos.tpl');

?>