<?php

// Init view
View::initView('mobile_index');
Plugin::triggerEvent('mobile_index.start');

// Verify if user is logged in
$userService = new UserService();
View::$vars->loggedInUser = $userService->loginCheck();

// Establish page variables, objects, arrays, etc
$videoMapper = new VideoMapper();
View::$vars->meta->title = Language::GetText ('mobile_heading', array ('sitename' => $config->sitename));

// Retrieve Featured Video
View::$vars->featured_video = $videoMapper->getMultipleVideosByCustom(array(
    'status' => 'approved',
    'featured' => '1',
    'private' => '0',
    'gated' => '0'
));

// Retrieve Recent Videos
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' AND gated = '0' ORDER BY video_id DESC LIMIT 3";
$recentResults = $db->fetchAll($query);
View::$vars->recent_videos[] = $videoMapper->getVideosFromList(
    Functions::flattenArray($recentResults, 'video_id')
);

// Output Page
Plugin::Trigger ('mobile_index.before_render');
View::Render ('index.tpl');