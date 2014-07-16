<?php

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
$db = Registry::get('db');
$query = "SELECT video_id FROM " . DB_PREFIX . "videos WHERE status = 'approved' ORDER BY video_id DESC LIMIT :start, 20";
$videoMapper = new VideoMapper();
$videoResults = $db->fetchAll($query, array(':start' => $start));
$videoList = $videoMapper->getVideosFromList(Functions::arrayColumn($videoResults, 'video_id'));

// Output video list in requested format
if ($block) {
    $this->view->InitView();
    ob_start();
    $this->view->RepeatingBlock ($block, $videoList);
    $output = ob_get_contents();
    ob_end_clean();
} else {
    $output = json_encode ($videoList);
}

echo $output;