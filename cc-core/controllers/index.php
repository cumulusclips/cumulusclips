<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');
App::LoadClass ('Rating');


// Establish page variables, objects, arrays, etc
View::InitView ('index');
Plugin::Trigger ('index.start');
View::$vars->logged_in = User::LoginCheck();
if (View::$vars->logged_in) View::$vars->user = new User (View::$vars->logged_in);



// Retrieve Featured Video
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND featured = 1 AND private = '0'";
$result_featured = $db->Query ($query);
View::$vars->featured_videos = array();
while ($video = $db->FetchObj ($result_featured)) View::$vars->featured_videos[] = $video->video_id;



// Retrieve Recent Videos
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' ORDER BY video_id DESC LIMIT 6";
$result_recent = $db->Query ($query);
View::$vars->recent_videos = array();
while ($video = $db->FetchObj ($result_recent)) View::$vars->recent_videos[] = $video->video_id;


// Output Page
Plugin::Trigger ('index.before_render');
View::Render ('index.tpl');

?>