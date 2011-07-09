<?php

### Created on February 28, 2009
### Created by Miguel A. Hurtado
### This script displays the site homepage


// Include required files
include ('../cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Filesystem');


// Establish page variables, objects, arrays, etc
Plugin::Trigger ('admin.videos.start');
//$logged_in = User::LoginCheck(HOST . '/login/');
//$admin = new User ($logged_in);
$page_title = 'Update Complete!';
$tmp = DOC_ROOT . '/.updates';
$log = $tmp . '/status';





### Check for updates
// Phone home and poll for updates - cURL vs AJAX ?
    // No updates
    // Updates avail. - Provide new version num & URL to update.xml


//Initializing update...
//Downloading files...
//Applying changes...
//Clean up...
//Update Complete!





/*****************
INITIALIZE UPDATES
*****************/

### Create hidden temp dir
Filesystem::Open();
if (!Filesystem::CreateDir ($tmp)) exit('Error 1');
if (!Filesystem::Create ($log)) exit ('Error 2');

// Update log
if (!Filesystem::Write ($log, "<p>Initializing update&hellip;</p>\n")) exit ('Error 3');


### De-activate plugins
### De-activate themes


### Load update.xml
$update_location = 'http://cumulusclips.org/updates/update.xml';
$xml = simplexml_load_file ($update_location);





/*************
DOWNLOAD FILES
*************/

// Update log
if (!Filesystem::Write ($log, "<p>Downloading files&hellip;</p>\n")) exit ('Error 4');


### Download modified files
foreach ($xml->modified->file as $file) {
    $local_file = $tmp . '/' . md5 ($file);
    $content = file_get_contents ($file);
    if (!Filesystem::Create ($local_file)) exit ('Error 5');
    if (!Filesystem::Write ($local_file, $content)) exit ('Error 6');
}


### Download new files
foreach ($xml->added->file as $file) {
    $local_file = $tmp . '/' . md5 ($file);
    $content = file_get_contents ($file);
    if (!Filesystem::Create ($local_file)) exit ('Error 7');
    if (!Filesystem::Write ($local_file, $content)) exit ('Error 8');
}


### Download database changes
$local_file = $tmp . '/' . md5 ($xml->database) . '.php';
$content = file_get_contents ($xml->database);
if (!Filesystem::Create ($local_file)) exit ('Error 9');
if (!Filesystem::Write ($local_file, $content)) exit ('Error 10');





/************
APPLY CHANGES
************/

// Update log
if (!Filesystem::Write ($log, "<p>Applying changes&hellip;</p>\n")) exit ('Error 11');


### Save temp. modifcations to perm. locations
foreach ($xml->modified->file as $file) {
    $local_file = $tmp . '/' . md5 ($file);
    $file = str_replace ('http://cumulusclips.org/updates', DOC_ROOT, $file);
    if (!Filesystem::Copy ($local_file, $file)) exit ('Error 12');
}


### Save temp. additions to perm. locations
foreach ($xml->added->file as $file) {
    $local_file = $tmp . '/' . md5 ($file);
    $file = str_replace ('http://cumulusclips.org/updates', DOC_ROOT, $file);
    if (!Filesystem::Copy ($local_file, $file)) exit ('Error 13');
}


### Delete Deprecated files


### Execute DB change queries
$db_change_file = $tmp. '/' . md5 ($xml->database) . '.php';
include ($db_change_file);





/*******
CLEAN UP
*******/

// Update log
if (!Filesystem::Write ($log, "<p>Clean up&hellip;</p>\n")) exit ('Error 12');

### Delete temp. dir.
if (!Filesystem::Delete ($tmp)) exit('Error 13');


### Activate themes
### Activate plugins
Filesystem::Close();



// Output Header
include ('header.php');

?>

<div id="updates-complete">

    <h1>Update Complete!</h1>

    <div class="block">
        <p>You are now running the latest version of CumulusClips. Don't forget
        to re-enable all your plugins and themes.</p>
    </div>
    
</div>

<?php include ('footer.php'); ?>