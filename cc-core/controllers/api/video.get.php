<?php

$this->view->options->disableView = true;
$videoMapper = new VideoMapper();

// Verify a video was selected
if (empty($_GET['videoId']) || !is_numeric($_GET['videoId']) || $_GET['videoId'] < 1) App::Throw404();

// Check if video is valid
$video = $videoMapper->getVideoByCustom(array('video_id' => $_GET['videoId'], 'status' => 'approved'));
if (!$video) App::Throw404();

// Output response
$apiResponse = new ApiResponse();
$apiResponse->result = true;
$apiResponse->data = $video;
echo json_encode($apiResponse);