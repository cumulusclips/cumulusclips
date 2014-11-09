<?php

$this->view->options->disableView = true;

// Verify valid list was provided
if (!empty($_GET['list']) && preg_match('/^[0-9]+(,[0-9]+)*$/', $_GET['list'])) {
    $videoMapper = new VideoMapper();
    $videoIdList = explode(',', $_GET['list']);
    $videoList = $videoMapper->getVideosFromList($videoIdList);
    foreach ($videoList as $key => $video) {
        if ($video->status != 'approved') unset($videoList[$key]);
    }
} else {
    App::Throw404();
}

// Output response
$apiResponse = new ApiResponse();
$apiResponse->result = true;
$apiResponse->data = $videoList;
echo json_encode($apiResponse);