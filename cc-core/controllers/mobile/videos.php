<?php

// Init view
View::initView('mobile_videos');
Plugin::triggerEvent('mobile_videos.start');

// Verify if user is logged in
$userService = new UserService();
View::$vars->loggedInUser = $userService->loginCheck();

// Establish page variables, objects, arrays, etc
$videoMapper = new VideoMapper();

// Retrieve video count
$query = "SELECT COUNT(video_id) FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' AND gated = '0'";
$db->fetchRow($query);
View::$vars->count = $db->rowCount();

// Retrieve video list
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' AND gated = '0' ORDER BY video_id DESC LIMIT 20";
View::$vars->videos = array();
$videoResults = $db->fetchAll($query);
View::$vars->videos = $videoMapper->getVideosFromList(
    Functions::flattenArray($videoResults, 'video_id')
);

// Output Page
Plugin::Trigger ('mobile_videos.before_render');
View::Render ('videos.tpl');