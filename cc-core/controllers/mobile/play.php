<?php

// Include required files
include_once (dirname (dirname (dirname (__FILE__))) . '/config/bootstrap.php');
App::LoadClass ('Video');


// Establish page variables, objects, arrays, etc
View::InitView ('mobile_play');
Plugin::Trigger ('mobile_play.start');


// Verify a video was selected
if (!isset ($_GET['vid']) || !is_numeric ($_GET['vid'])) App::Throw404();


// Verify video exists
$data = array ('video_id' => $_GET['vid'], 'status' => 'approved');
$id = Video::Exist ($data);
if (!$id) App::Throw404();


// Retrieve video
View::$vars->video = $video = new Video ($id);
View::$vars->meta->title = $video->title;


// Output Page
Plugin::Trigger ('mobile_play.before_render');
View::Render ('play.tpl');

?>