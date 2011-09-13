<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/config/bootstrap.php');
App::LoadClass ('Video');


if (!isset ($_POST['submitted']) || $_POST['submitted'] != 'true') App::Throw404();
if (empty ($_POST['keyword']) || ctype_space ($_POST['keyword'])) App::Throw404();


// Validate starting record
if (!empty ($_POST['start']) && is_numeric ($_POST['start'])) {
    $start = $_POST['start'];
} else {
    $start = 0;
}


// Validate block output format
$block = (isset ($_POST['block'])) ? $_POST['block'] . '.tpl' : null;


// Retrieve video list
$keyword = $db->Escape (trim ($_POST['keyword']));
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' AND MATCH(title, tags, description) AGAINST('$keyword') LIMIT $start, 20";
$videos = array();
$result = $db->Query ($query);
while ($video = $db->FetchObj ($result)) $videos[] = $video->video_id;


// Output video list in requested format
if ($block) {

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