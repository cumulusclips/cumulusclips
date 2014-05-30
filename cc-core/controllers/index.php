<?php

Plugin::Trigger('index.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();

// Retrieve Featured Video
$videoMapper = new VideoMapper();
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND featured = 1 AND private = '0'";
$featuredVideosResults = $db->fetchAll($query);
$view->vars->featured_videos = $videoMapper->getVideosFromList(
    Functions::arrayColumn($featuredVideosResults, 'video_id')
);

// Retrieve Recent Videos
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND private = '0' ORDER BY video_id DESC LIMIT 6";
$recentVideosResults = $db->fetchAll($query);
$view->vars->recent_videos = $videoMapper->getVideosFromList(
    Functions::arrayColumn($recentVideosResults, 'video_id')
);

Plugin::Trigger('index.before_render');