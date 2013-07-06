<?php

// Include required files
include_once(dirname(dirname(__FILE__)) . '/cc-core/config/admin.bootstrap.php');
App::LoadClass('User');

// Establish page variables, objects, arrays, etc
Functions::RedirectIf($logged_in = User::LoginCheck(), HOST . '/login/');
$admin = new User($logged_in);
Functions::RedirectIf(User::CheckPermissions('admin_panel', $admin), HOST . '/myaccount/');
$page_title = 'Add New Plugin';
$message = null;
$message_type = null;
$admin_js[] = ADMIN . '/extras/uploadify/uploadify.plugin.js';
$admin_js[] = ADMIN . '/js/uploadify.js';
$admin_css[] = ADMIN . '/extras/uploadify/uploadify.css';
$clean_up = true;

// Handle Upload Form
if (isset($_POST['submitted'])) {

    // Validate file upload
    if (!empty($_POST['tempFile']) && file_exists($_POST['tempFile'])) {
        
        // Extract zip archive and move theme
        try {

            // Extract theme
            $tempDirectory = dirname($_POST['tempFile']);
            Filesystem::Open();
            Filesystem::Extract($_POST['tempFile']);

            // Check for duplicates
            $temp_contents = array_diff(scandir($tempDirectory), array('.', '..', 'addon.zip'));
            $themeName = array_pop($temp_contents);
            if (file_exists(DOC_ROOT . '/cc-content/themes/' . $themeName)) {
                throw new Exception("Theme cannot be added. It conflicts with another theme.");
            }

            // Copy theme contents to themes dir
            Filesystem::CopyDir($tempDirectory . '/' . $themeName, DOC_ROOT . '/cc-content/themes/' . $themeName);

            // Validate theme
            if (!Functions::ValidTheme($themeName)) {
                throw new Exception("Theme contains errors. Please report this to it's developer");
            }

            // Clean up
            $clean_up = false;
            Filesystem::Delete($tempDirectory);
            Filesystem::Close();

            // Display success message
            $xml = simplexml_load_file(THEMES_DIR . '/' . $themeName . '/theme.xml');
            $message = $xml->name . ' has been added.';
            $message_type = 'success';

        } catch (Exception $e) {

            $message = $e->getMessage();
            $message_type = 'error';

            // Perform clean up if plugin contained errors
            if ($clean_up) {
                try {
                    Filesystem::Delete($tempDirectory);
                    Filesystem::Close();
                } catch (Exception $e) {
                    $message = $e->getMessage();
                    $message_type = 'error';
                }
            }

        }   //  END extract and move theme

    } else {
        $message = 'Invalid file upload';
        $message_type = 'error';
    }   // END check for form errors

}

// Output Header
include('header.php');

?>
<div id="plugins-add">

    <h1>Add New Theme</h1>

    <div class="message <?=$message_type?>"><?=$message?></div>

    <div class="block">

        <p class="row-shift">If you have a theme in .zip format use this form
        to upload and add it to the system.</p>

        <form name="uploadify" action="<?=ADMIN?>/themes_add.php" method="post">

            <div class="row">
                <label>Theme Zip File:</label>
                <input id="upload" type="file" name="upload" />
                <input id="upload_button" class="button" type="button" value="Upload" />
                <input type="hidden" name="uploadLimit" value="<?=1024*1024*100?>" />
                <input type="hidden" name="fileTypes" value="<?=htmlspecialchars(json_encode(array('zip')))?>" />
                <input type="hidden" name="uploadType" value="addon" />
                <input type="hidden" name="tempFile" value="" />
                <input type="hidden" name="submitted" value="true" />
            </div>

            <div id="upload_status">
                <div class="title"></div>
                <div class="progress">
                    <a href="" title="Cancel">Cancel</a>
                    <div class="meter">
                        <div class="fill"></div>
                    </div>
                    <div class="percentage">0%</div>
                </div>
            </div>
            
        </form>

    </div>

</div>

<?php include('footer.php'); ?>