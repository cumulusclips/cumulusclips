<?php

Plugin::triggerEvent('mobile_play.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();

// Establish page variables, objects, arrays, etc
$videoMapper = new VideoMapper();

// Verify a video was selected
if (empty($_GET['vid']) || !is_numeric($_GET['vid']) || $_GET['vid'] < 1) App::Throw404();

// Verify video exists
$video = $videoMapper->getVideoByCustom(array(
    'video_id' => $_GET['vid'],
    'status' => 'approved',
    'private' => '0',
    'gated' => '0'
));
if (!$video) App::Throw404();

// Retrieve video
$view->vars->video = $video;
$view->vars->meta->title = $video->title;

Plugin::Trigger ('mobile_play.before_render');