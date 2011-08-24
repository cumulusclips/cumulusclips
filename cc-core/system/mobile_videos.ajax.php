<?php

### Created on October 23, 2010
### Created by Miguel A. Hurtado
### This script retrieves more videos to display on the videos page


// Include required files
include_once (dirname (dirname (__FILE__)) . '/config/bootstrap.php');
App::LoadClass ('Video');


if (!isset ($_POST['submitted']) || $_POST['submitted'] != 'true') App::Throw404();


// Validate starting record
if (!empty ($_POST['start']) && is_numeric ($_POST['start'])) {
    $start = $_POST['start'];
} else {
    $start = 0;
}


// Validate block output format
$block = (isset ($_POST['block'])) ? $_POST['block'] . '.tpl' : null;


// Retrieve video list
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' ORDER BY video_id DESC LIMIT $start, 20";
$videos = array();
$result = $db->Query ($query);
while ($video = $db->FetchObj ($result)) $videos[] = $video->video_id;


// Output video list in requested format
if ($format == 'html') {

    View::InitView();
    ob_start();
    View::RepeatingBlock ($block, $videos);
    $output = ob_get_contents();
    ob_end_clean();

} else {
    $output = json_encode ($videos);
}

echo $output;

?>