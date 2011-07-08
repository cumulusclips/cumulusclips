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
$message = null;
$page_title = 'Begin Update';
$header = 'Begin Update';


### Handle "Delete" theme if requested
if (!empty ($_GET['delete']) && !ctype_space ($_GET['delete'])) {

    // Validate theme
    if (file_exists (THEMES_DIR . '/' . $_GET['delete'] . '/theme.xml')) {

        $xml = simplexml_load_file (THEMES_DIR . '/' . $_GET['delete'] . '/theme.xml');
        if (Settings::Get('active_theme') != $_GET['delete']) {
            // DELETE THEME CODE
            $message = $xml->name . ' theme has been deleted';
            $message_type = 'success';
        } else {
            $message = 'Active theme cannot be deleted. Activate another theme and then try again';
            $message_type = 'error';
        }

    }

}


//$xml = simplexml_load_file (DOC_ROOT . '/updates.xml');
//Filesystem::Open();
//Filesystem::CreateDir (DOC_ROOT . '/.updates');
//Filesystem::SetPermissions (DOC_ROOT . '/.updates', 0777);
//echo '<pre>', print_r (glob(DOC_ROOT . '/*'),true), '</pre>';

### Check for updates
// Phone home and poll for updates - cURL vs AJAX ?
    // No updates
    // Updates avail. - Provide new version num & URL to update.xml


### Begin updates
// Load update.xml
// De-activate plugins
// De-activate themes


### Download updates
// Create hidden temp dir (FTP)
// Loop through modifications
    // Save temp files locally with md5 hash as names (FTP)
// Loop through additions
    // Save temp files locally with md5 hash as names (FTP)


### Apply updates
// Loop through additions
    // Save temp files in new locations (FTP)
// Loop through modifications
    // Overwrite old files with new content from temp. (FTP)
// Loop through removals
    // Delete files (FTP)


### Clean up
// Delete hidden temp dir
// Activate themes
// Activate plugins


// Output Header
include ('header.php');

?>

<div id="begin-update">

    <h1>Begin Update</h1>

    <div class="block">
        <p>You're about to update your system. Your site will be unusable during
        this process and any visitors will see a 'Maintenance Mode' message.</p>
        
        <p>Be sure to backup you database and any changes made to your system
        before you begin the update.</p>

        <p><a class="button begin-update" href="<?=ADMIN?>/updates_execute.php">Begin Update</a></p>
    </div>
    
</div>

<div id="update-in-progress">

    <h1>Update in Progress&hellip;</h1>

    <div class="block">
        <p>CumulusClips is currently performing updates. <strong>DO NOT</strong>
        close or refresh this page. Doing so will cause incomplete or even
        failed installation and you will have to manually update.</p>

        <p>This page may <em>seem</em> unresponsive however it is working in the
        background, we promise.</p>

        <p class="working">Working&hellip;</p>

        <p class="status"></p>
    </div>

</div>

<?php include ('footer.php'); ?>