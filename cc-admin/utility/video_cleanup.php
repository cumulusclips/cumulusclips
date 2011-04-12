<?php

### Created on November 13, 2009
### Created by Miguel A. Hurtado
### This script deletes any orphaned files, and DB records for videos that did not complete processing
### This script must be executed from the root of the site


// Include required files
include ($_SERVER['DOCUMENT_ROOT'] . '/config/bootstrap.php');
include (DOC_ROOT . '/includes/functions.php');
App::LoadClass ('DBConnection.php');
App::LoadClass ('KillApp.php');


// Establish page variables, objects, arrays, etc
$KillApp = new KillApp;
$db = new DBConnection ($KillApp);
$invalid_videos = 0;
$vids = array();
$delete_flv = array();
$delete_mp4 = array();
$delete_thumbs = array();



// Pull list of active videos
echo '<p>Video Cleanup Started...</p>';
$query = "SELECT filename FROM videos WHERE status = 6";
$result = $db->Query ($query);
while ($row = mysql_fetch_row ($result)) {
    $vids[] = $row[0];
}







// Check FLV directory
$dir = dirname (__FILE__) . '/uploads';
$dh = opendir ($dir);
while (($file = readdir ($dh)) !== FALSE) {

    if ($file == '.' || $file == '..' || $file == 'mp4' || $file == 'thumbs' || $file == 'temp' || $file == 'pictures' || $file == '.svn') {
        continue;
    }

    $file = substr ($file, 0, -4);
    if (!in_array ($file, $vids)) {
        $delete_flv[] = $file;
    }

}
closedir ($dh);







// Check MP4 directory
$dir_mp4 = dirname (__FILE__) . '/uploads/mp4';
$dh = opendir ($dir_mp4);
while (($file = readdir ($dh)) !== FALSE) {

    if ($file == '.' || $file == '..' || $file == '.svn') {
        continue;
    }

    $file = substr ($file, 0, -4);
    if (!in_array ($file, $vids)) {
        $delete_mp4[] = $file;
    }

}
closedir ($dh);







// Check thumbs directory
$dir_thumbs = dirname (__FILE__) . '/uploads/thumbs';
$dh = opendir ($dir_thumbs);
while (($file = readdir ($dh)) !== FALSE) {

    if ($file == '.' || $file == '..' || $file == '.svn') {
        continue;
    }

    $file = substr ($file, 0, -4);
    if (!in_array ($file, $vids)) {
        $delete_thumbs[] = $file;
    }

}
closedir ($dh);







// Delete invalid videos
$query = "DELETE FROM videos WHERE status != 6";
$db->Query ($query);
$invalid_videos = $db->Affected();


echo 'Valid Video Count: ', count ($vids), '<br />';
echo 'Invalid FLV Count: ', count ($delete_flv), '<br />';
echo 'Invalid MP4 Count: ', count ($delete_mp4), '<br />';
echo 'Invalid Thumb Count: ', count ($delete_thumbs), '<br />';
echo 'Invalid DB Record Count: ', $invalid_videos, '<br />';







// Delete FLV
echo 'Deleting Orphaned FLVs...<br />';
if (!empty ($delete_flv)) {

    echo '<p>The following FLVs were deleted:<br />';
    foreach ($delete_flv as $value) {
        $file = UPLOAD_PATH . '/' . $value . '.flv';
        if (is_file ($file)) {
            unlink ($file);
            echo $file, '<br />';
        }
    }

    echo 'FLV Deletion Complete!</p>';
    
}






// Delete MP4
echo 'Deleting Orphaned MP4s...<br />';
if (!empty ($delete_mp4)) {

    echo '<p>The following MP4s were deleted:<br />';
    foreach ($delete_mp4 as $value) {
        $file = UPLOAD_PATH . '/mp4/' . $value . '.mp4';
        if (is_file ($file)) {
            unlink ($file);
            echo $file, '<br />';
        }
    }

    echo 'MP4 Deletion Complete!</p>';

}







// Delete Thumbs
echo 'Deleting Orphaned Thumbs...<br />';
if (!empty ($delete_thumbs)) {

    echo '<p>The following thumbs were deleted:<br />';
    foreach ($delete_thumbs as $value) {
        $file = UPLOAD_PATH . '/thumbs/' . $value . '.jpg';
        if (is_file ($file)) {
            unlink ($file);
            echo $file, '<br />';
        }
    }

    echo 'Thumb Deletion Complete!</p>';

}

echo '<p>Video Cleanup Complete!</p>';

?>