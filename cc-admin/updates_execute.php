<?php
sleep(10);
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

<div id="updates-complete">

    <h1>Update Complete!</h1>

    <div class="block">
        <p>You are now running the latest version of CumulusClips. Don't forget
        to re-enable all your plugins.</p>
    </div>
    
</div>

<?php include ('footer.php'); ?>