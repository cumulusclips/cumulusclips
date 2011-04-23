<?php

### Created on February 28, 2009
### Created by Miguel A. Hurtado
### This script displays the site homepage


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');
App::LoadClass ('Rating');
App::LoadClass ('Picture');
View::InitView();


// Establish page variables, objects, arrays, etc
View::LoadPage ('index');
View::$vars->logged_in = User::LoginCheck();
if (View::$vars->logged_in) $user = new User (View::$vars->logged_in);



// Retrieve Featured Video
$query = "SELECT video_id FROM videos WHERE featured = 1";
$result = $db->Query ($query);
$row = $db->FetchObj ($result);
View::$vars->featured = new Video ($row->video_id);


// Retrieve Recent Videos
$query = "SELECT video_id FROM videos WHERE status = 6 ORDER BY video_id DESC LIMIT 3";
View::$vars->result_recent = $db->Query ($query);


// Output Page
View::AddJs ('jcycle.plugin.js');
View::AddJs ('slideshow.js');
View::AddSidebarBlock ('home_login.tpl');
View::Render ('index.tpl');

?>