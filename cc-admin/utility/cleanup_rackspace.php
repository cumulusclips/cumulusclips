<?php

### Created on July 6, 2010
### Created by Miguel A. Hurtado
### This script deletes files from RackSpace that aren't valid videos


// Include required files
include ($_SERVER['DOCUMENT_ROOT'] . '/config/bootstrap.php');
include (DOC_ROOT . '/includes/functions.php');
App::LoadClass ('DBConnection.php');
App::LoadClass ('KillApp.php');
App::LoadClass ('Video.php');
include (DOC_ROOT . '/vendors/rackspace_cloud_api/cloudfiles.php');


// Establish page variables, objects, arrays, etc
$KillApp = new KillApp;
$db = new DBConnection ($KillApp);


// Connect to RackSpace
$auth = new CF_Authentication ('mahurtado66', 'd8b78c654d1e6eb572e7b0d41d2d9ce8');
$auth->authenticate();
$conn = new CF_Connection ($auth);
$flv_bucket = $conn->get_container ('flv');
$mp4_bucket = $conn->get_container ('mp4');
$thumbs_bucket = $conn->get_container ('thumbs');



### Loop through FLVs
foreach ($flv_bucket->list_objects() as $value) {

    $base = substr ($value, 0, -4);
    $data = array ('filename' => $base);
    $video_id = Video::Exist ($data, $db);
    if (!$video_id) {
//        $bucket->delete_object ($value);
    }

}



### Loop through  MP4s
foreach ($mp4_bucket->list_objects() as $value) {

    $base = substr ($value, 0, -4);
    $data = array ('filename' => $base);
    $video_id = Video::Exist ($data, $db);
    if (!$video_id) {
//        $bucket->delete_object ($value);
    }

}



### Loop through thumbs
foreach ($thumbs_bucket->list_objects() as $value) {

    $base = substr ($value, 0, -4);
    $data = array ('filename' => $base);
    $video_id = Video::Exist ($data, $db);
    if (!$video_id) {
//        $bucket->delete_object ($value);
    }

}

?>