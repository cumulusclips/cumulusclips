<?php

// Init view
$view->initView('mobile_videos', true);
Plugin::triggerEvent('mobile_videos.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();

// Establish page variables, objects, arrays, etc
$videoMapper = new VideoMapper();

// Retrieve video count
$query = "SELECT COUNT(video_id) FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' AND gated = '0'";
$db->fetchRow($query);
$view->vars->count = $db->rowCount();

// Retrieve video list
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' AND gated = '0' ORDER BY video_id DESC LIMIT 20";
$view->vars->videos = array();
$videoResults = $db->fetchAll($query);
$view->vars->videos = $videoMapper->getVideosFromList(
    Functions::flattenArray($videoResults, 'video_id')
);

// Output Page
Plugin::Trigger ('mobile_videos.before_render');
$view->Render ('videos.tpl');