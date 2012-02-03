<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/config/bootstrap.php');
App::LoadClass ('Video');

try {

    // Verify video is available
    if (empty ($_GET['vid']) && !is_numeric ($_GET['vid'])) throw new Exception();

    // Validate given video
    if (!Video::Exist (array ('video_id' => $_GET['vid'], 'status' => 'approved', 'gated' => '0', 'private' => '0'))) throw new Exception();

    // Load Video
    $video = new Video ($_GET['vid']);

} catch (Exception $e) {
    $video = null;
}


// Output player
include ($config->theme_path . '/embed.tpl');

?>