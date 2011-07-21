<?php

### Created on July 3, 2009
### Created by Miguel A. Hurtado
### This script displays the homepage for the mobile site


// Include required files
include_once (dirname (dirname (dirname (__FILE__))) . '/config/bootstrap.php');
App::LoadClass ('Video');


// Establish page variables, objects, arrays, etc
View::InitView ('index');
Plugin::Trigger ('index.start');


// Retrieve Featured Video
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND featured = 1";
$result_featured = $db->Query ($query);


// Retrieve Recent Videos
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' ORDER BY video_id DESC LIMIT 3";
View::$vars->result_recent = $db->Query ($query);


// Output Page
Plugin::Trigger ('mobile_index.before_render');
View::Render ('mobile/index.tpl');

?>