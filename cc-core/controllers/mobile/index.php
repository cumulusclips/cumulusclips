<?php

Plugin::triggerEvent('mobile_index.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();

// Establish page variables, objects, arrays, etc
$videoMapper = new VideoMapper();
$view->vars->meta->title = Language::GetText ('mobile_heading', array ('sitename' => $config->sitename));

// Retrieve Featured Video
$view->vars->featuredVideos = $videoMapper->getMultipleVideosByCustom(array(
    'status' => 'approved',
    'featured' => '1',
    'private' => '0',
    'gated' => '0'
));

// Retrieve Recent Videos
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' AND gated = '0' ORDER BY video_id DESC LIMIT 3";
$recentResults = $db->fetchAll($query);
$view->vars->recentVideos = $videoMapper->getVideosFromList(
    Functions::arrayColumn($recentResults, 'video_id')
);

Plugin::Trigger ('mobile_index.before_render');