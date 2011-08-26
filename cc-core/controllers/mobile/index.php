<?php

### Created on July 3, 2009
### Created by Miguel A. Hurtado
### This script displays the homepage for the mobile site


// Include required files
include_once (dirname (dirname (dirname (__FILE__))) . '/config/bootstrap.php');
App::LoadClass ('Video');


// Establish page variables, objects, arrays, etc
View::InitView ('mobile_index');
Plugin::Trigger ('mobile_index.start');


// Retrieve updated page title
View::$vars->meta->title = Language::GetText ('mobile_heading', array ('sitename' => $config->sitename));


// Retrieve Featured Video
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND featured = 1";
View::$vars->featured_video = array();
$result_featured = $db->Query ($query);
while ($video = $db->FetchObj ($result_featured)) View::$vars->featured_video[] = $video->video_id;


// Retrieve Recent Videos
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' ORDER BY video_id DESC LIMIT 3";
View::$vars->recent_videos = array();
$result_recent = $db->Query ($query);
while ($video = $db->FetchObj ($result_recent)) View::$vars->recent_videos[] = $video->video_id;


// Output Page
Plugin::Trigger ('mobile_index.before_render');
View::Render ('index.tpl');

?>