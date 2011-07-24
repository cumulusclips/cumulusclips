<?php

### Created on July 4, 2009
### Created by Miguel A. Hurtado
### This script allows users to search for videos on the mobile site


// Include required files
include_once (dirname (dirname (dirname (__FILE__))) . '/config/bootstrap.php');
App::LoadClass ('Video');


// Establish page variables, objects, arrays, etc
View::InitView ('mobile_search');
Plugin::Trigger ('mobile_search.start');


// Handle form if submitted
if (!empty ($_POST['keyword']) && !ctype_space ($_POST['keyword'])) {

    // Retrieve search result count
    View::$vars->keyword = $keyword = $db->Escape (trim ($_POST['keyword']));
    $query = "SELECT COUNT(video_id) FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND MATCH(title, tags, description) AGAINST('$keyword')";
    $result = $db->query ($query);
    View::$vars->count = $db->FetchRow ($result);
    View::$vars->count = View::$vars->count[0];

    // Retrieve search result video list
    $query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND MATCH(title, tags, description) AGAINST('$keyword') LIMIT 20";
    $result = $db->query ($query);
    View::$vars->search_videos = array();
    while ($video = $db->FetchObj ($result)) View::$vars->search_videos[] = $video->video_id;

}


// Output Page
Plugin::Trigger ('mobile_search.before_render');
View::Render ('search.tpl');

?>