<?php

// Init view
View::InitView('index');
Plugin::Trigger('index.start');

// Verify if user is logged in
$userService = new UserService();
View::$vars->loggedInUser = $userService->loginCheck();

// Retrieve Featured Video
$videoMapper = new VideoMapper();
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND featured = 1 AND private = '0'";
$featuredVideosResults = $db->fetchAll($query);
View::$vars->featured_videos = $videoMapper->getMultipleVideosById(
    Functions::flattenArray($featuredVideosResults, 'video_id')
);

// Retrieve Recent Videos
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' ORDER BY video_id DESC LIMIT 6";
$recentVideosResults = $db->fetchAll($query);
View::$vars->recent_videos = $videoMapper->getMultipleVideosById(
    Functions::flattenArray($recentVideosResults, 'video_id')
);

// Output Page
Plugin::Trigger('index.before_render');
View::Render('index.tpl');