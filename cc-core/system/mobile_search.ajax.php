<?php

### Created on October 24, 2010
### Created by Miguel A. Hurtado
### This script retrieves more videos to display on the search page


// Include required files
include ($_SERVER['DOCUMENT_ROOT'] . '/__Restricted/__Config.php');
include (MAIN_ROOT . '/includes/functions.php');
include (LIB . '/DBConnection.php');
include (LIB . '/KillApp.php');
include (LIB . '/Video.php');


// Establish page variables, objects, arrays, etc
session_start();
$KillApp = new KillApp;
$db = new DBConnection ($KillApp);

//echo '<pre>',print_r ($_POST,true),'</pre>';
//exit();

if (!isset ($_POST['submitted']) || $_POST['submitted'] != 'true') {
    exit();
}

if (!empty ($_POST['start']) && is_numeric($_POST['start']) && $_POST['start'] > 0) {
    $start = $_POST['start'];
} else {
    exit();
}

if (!empty ($_POST['keyword']) && !ctype_space ($_POST['keyword'])) {
    $keyword = trim ($_POST['keyword']);
    $query = "SELECT video_id FROM videos WHERE status = 6 AND MATCH(title, tags, description) AGAINST('$keyword') LIMIT $start, $config->max_list";
    $result = $db->query ($query);
} else {
    exit();
}

?>

<?php if ($db->Count ($result) > 0): ?>

    <?php while ($row = $db->FetchRow ($result)): ?>

        <?php $video = new Video ($row[0], $db); ?>
        <div onclick="window.location = '/v/<?=$video->video_id?>/';" class="video">
            <img class="thumb" src="<?=$config->thumb_bucket_url?>/<?=$video->filename?>.jpg" height="56" width="75" />
            <p class="title"><?=$video->title?></p>
            <strong>Duration:</strong><?=$video->duration?>
            <div class="clear"></div>
        </div>

    <?php endwhile; ?>

<?php endif; ?>