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
$update_location = UPDATE_URL . '/latest';
$tmp = DOC_ROOT . '/.updates';
$log = $tmp . '/status';


// Verify updates are available and user confirmed to begin update
//$update = Functions::UpdateCheck();
//if (isset ($_GET['update'], $_SESSION['begin_update']) && $update && $_SESSION['begin_update'] <= time()-300) {
//    unset ($_SESSION['begin_update']);
//} else {
//    header ("Location: " . ADMIN . '/updates.php');
//}





/*****************
INITIALIZE UPDATES
*****************/

### Create hidden temp dir
Filesystem::Open();
if (!Filesystem::CreateDir ($tmp)) exit('Error initializing - Unable to create temp. directory');
if (!Filesystem::SetPermissions($tmp, 0777)) exit('Error initializing - Unable to set persmissions on temp directory');
if (!Filesystem::Create ($log)) exit ('Error initializing - Unable to create log file');
// Update log
if (!Filesystem::Write ($log, "<p>Initializing update&hellip;</p>\n")) exit ('Error 1');


### De-activate plugins
### De-activate themes


### Load update.xml





/***************
DOWNLOAD PACKAGE
***************/

// Update log
if (!Filesystem::Write ($log, "<p>Downloading package&hellip;</p>\n")) exit ('Error 2');

### Download archive
$zip_content = file_get_contents ($update_location . '/update.zip');
$zip_file = $tmp . '/update.zip';
if (!Filesystem::Create ($zip_file)) exit ('Error downloading files - Unable to create archive');
if (!Filesystem::Write ($zip_file, $zip_content)) exit ('Error downloading files - Unable to save archive');
if (md5_file ($zip_file) != '9de16ab2a9ee4baa91ae6e353304249f') exit ("Error - Checksums don't match");


### Download patch file
$patch_file_content = file_get_contents (UPDATE_URL . '/patch.php?version=' . CURRENT_VERSION);
$patch_file = null;
if (!empty ($patch_file_content)) {
    $patch_file = $tmp . '/patch_file.php';
    if (!Filesystem::Create ($patch_file)) exit ('Error download files - Unable to create patch file');
    if (!Filesystem::Write ($patch_file, $patch_file_content)) exit ('Error downloading files - Unable to save patch file');
}





/***********
UNPACK FILES
***********/

// Update log
if (!Filesystem::Write ($log, "<p>Unpacking files&hellip;</p>\n")) exit ('Error 3');

### Extracting files
if (!Filesystem::Extract ($zip_file)) exit ('Error unpacking files - Unable to extract zip archive');

### Load patch file into memory
if ($patch_file) include_once ($patch_file);





/************
APPLY CHANGES
************/

// Update log
if (!Filesystem::Write ($log, "<p>Applying changes&hellip;</p>\n")) exit ('Error 10');

### Applying changes
if (!Filesystem::CopyDir ($tmp . '/cumulus', DOC_ROOT)) exit ('Error 11');
exit('Done');


### Perform patch file modifications
if ($patch_file) {

    reset ($perform_updates);
    foreach ($perform_updates as $version) {

        ### Execute DB modifications queries
        $db_update_queries = call_user_func ('db_update_' . $version);
        foreach ($db_update_queries as $query) $db->Query ($query);


        ### Delete files marked for removal
        $remove_files = call_user_func ('remove_files_' . $version);
        foreach ($remove_files as $file) if (!Filesystem::Delete (DOC_ROOT . $file)) exit ('Error 14');

    }

}





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