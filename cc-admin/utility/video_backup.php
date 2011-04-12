<?php

### Created on May 9, 2010
### Created by Miguel A. Hurtado
### This script receives notification that a video has finished processecing at Encoding.com and updates its status


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
$xml = NULL;
$result = NULL;
$container = array();


// Get list of videos marked as 'Processing'
$query = "SELECT video_id FROM videos WHERE status = 6 AND backup = 0";
$result = $db->Query ($query);
while ($row = $db->FetchRow ($result)) {

    $video = new Video ($row[0], $db);

    // Connect to Rackspace CloudFiles
    $auth = new CF_Authentication ($config->rs_user, $config->rs_key);
    $auth->authenticate();
    $conn = new CF_Connection ($auth);


    // Create FLV backup
    $flv_container = $conn->get_container ('flv');
    $flv_obj = $flv_container->get_object ($video->filename . '.flv');
    $flv = UPLOAD_PATH . '/' . $video->filename . '.flv';
    $flv_result = @$flv_obj->save_to_filename ($flv);


    // Create MP4 backup
    $mp4_container = $conn->get_container ('mp4');
    $mp4_obj = $mp4_container->get_object ($video->filename . '.mp4');
    $mp4 = UPLOAD_PATH . '/mp4/' . $video->filename . '.mp4';
    $mp4_result = @$mp4_obj->save_to_filename ($mp4);


    // Create Thumbnail backup
    $thumb_container = $conn->get_container ('thumbs');
    $thumb_obj = $thumb_container->get_object ($video->filename . '.jpg');
    $thumb = UPLOAD_PATH . '/thumbs/' . $video->filename . '.jpg';
    $thumb_result = @$thumb_obj->save_to_filename ($thumb);


    // Update backup status if backup was successful
    if ($flv_result && $mp4_result && $thumb_result) {
        $video->Update (array ('backup' => 1));
    } else {
        $msg = '';
        @mail (MAIN_EMAIL, 'Error occured during video backup', $msg);
        exit();
    }

}

?>