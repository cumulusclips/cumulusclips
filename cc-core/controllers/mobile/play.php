<?php

Plugin::triggerEvent('mobile_play.start');

// Verify if user is logged in
$userService = new UserService();
$this->view->vars->loggedInUser = $userService->loginCheck();

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
if (!$video) App::throw404();

// Retrieve video
$this->view->vars->video = $video;
$this->view->vars->meta->title = $video->title;

Plugin::triggerEvent('mobile_play.end');