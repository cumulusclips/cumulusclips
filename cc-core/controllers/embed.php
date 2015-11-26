<?php

$this->view->options->disableLayout = true;

try {
    // Verify video is available
    if (empty($_GET['vid']) || !is_numeric ($_GET['vid']) || $_GET['vid'] < 1) throw new Exception();

    // Validate given video
    $videoMapper = new VideoMapper();
    $video = $videoMapper->getVideoByCustom(array(
        'video_id' => $_GET['vid'],
        'status' => 'approved',
        'gated' => '0',
        'disable_embed' => '0'
    ));
    if (!$video) throw new Exception();
} catch (Exception $e) {
    $video = null;
}

$this->view->vars->webmEncodingEnabled = (Settings::get('webm_encoding_enabled') == '1') ? true : false;
$this->view->vars->theoraEncodingEnabled = (Settings::get('theora_encoding_enabled') == '1') ? true : false;
$this->view->vars->video = $video;
$video->views++;
$videoMapper->save($video);