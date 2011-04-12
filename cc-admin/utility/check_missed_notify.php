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
App::LoadClass ('Encoding.php');


// Establish page variables, objects, arrays, etc
$KillApp = new KillApp;
$db = new DBConnection ($KillApp);
$xml = NULL;
$result = NULL;
$container = array();


// Get list of videos marked as 'Processing'
$query = "SELECT job_id FROM videos WHERE status = 5";
$result = $db->Query ($query);
while ($job = $db->FetchRow ($result)) {

    // Retrieve job_id and check transcoding status
    $status = Encoding::GetEncodingStatus ($job[0]);
    if ($status) {

        // Call 'Notify' script if video completed transcoding
        Encoding::CallNotifyScript ($job[0], $status);

    }

}

?>