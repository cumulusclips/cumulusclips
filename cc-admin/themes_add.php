<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$userService = new UserService();
$adminUser = $userService->loginCheck();
Functions::redirectIf($adminUser, HOST . '/login/');
Functions::redirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$page_title = 'Add New Plugin';
$message = null;
$message_type = null;
$admin_js[] = ADMIN . '/extras/fileupload/fileupload.jquery-ui.widget.js';
$admin_js[] = ADMIN . '/extras/fileupload/fileupload.iframe-transport.js';
$admin_js[] = ADMIN . '/extras/fileupload/fileupload.plugin.js';
$admin_js[] = ADMIN . '/js/fileupload.js';
$clean_up = true;

// Handle Upload Form
if (isset($_POST['submitted'])) {

    // Validate file upload
    if (!empty($_POST['temp-file']) && file_exists($_POST['temp-file'])) {
        
        // Extract zip archive and move theme
        try {
            // Extract theme
            $tempDirectory = dirname($_POST['temp-file']);
            Filesystem::extract($_POST['temp-file']);

            // Check for duplicates
            $temp_contents = array_diff(scandir($tempDirectory), array('.', '..', 'addon.zip'));
            $themeName = array_pop($temp_contents);
            if (file_exists(DOC_ROOT . '/cc-content/themes/' . $themeName)) {
                throw new Exception("Theme cannot be added. It conflicts with another theme.");
            }

            // Copy theme contents to themes dir
            Filesystem::copyDir($tempDirectory . '/' . $themeName, DOC_ROOT . '/cc-content/themes/' . $themeName);

            // Validate theme
            if (!Functions::validTheme($themeName)) {
                throw new Exception("Theme contains errors. Please report this to it's developer");
            }

            // Clean up
            $clean_up = false;
            Filesystem::delete($tempDirectory);

            // Display success message
            $xml = simplexml_load_file(THEMES_DIR . '/' . $themeName . '/theme.xml');
            $message = $xml->name . ' has been added.';
            $message_type = 'success';

        } catch (Exception $e) {
            $message = $e->getMessage();
            $message_type = 'errors';

            // Perform clean up if plugin contained errors
            if ($clean_up) {
                try {
                    Filesystem::delete($tempDirectory);
                } catch (Exception $e) {
                    $message = $e->getMessage();
                    $message_type = 'errors';
                }
            }
        }   //  END extract and move theme
    } else {
        $message = 'Invalid file upload';
        $message_type = 'errors';
    }   // END check for form errors
}

// Output Header
$pageName = 'themes-add';
include('header.php');

?>

<!--[if IE 9 ]> <meta name="ie9" content="true" /> <![endif]-->

<h1>Add New Theme</h1>

<div class="message <?=$message_type?>"><?=$message?></div>

<div class="block">

    <p class="row-shift">If you have a theme in .zip format use this form
    to upload and add it to the system.</p>

    <form action="<?=ADMIN?>/themes_add.php" method="post">

        <div class="row">
            <label>Theme Zip File:</label>
            <div id="upload-select-file" class="button">
                <span>Browse</span>
                <input id="upload" type="file" name="upload" />
            </div>
            <input id="upload_button" class="button" type="button" value="Upload" />
            <input type="hidden" name="upload-limit" value="<?=1024*1024*100?>" />
            <input type="hidden" name="file-types" value="<?=htmlspecialchars(json_encode(array('zip')))?>" />
            <input type="hidden" name="upload-type" value="addon" />
            <input type="hidden" name="temp-file" value="" />
            <input type="hidden" name="upload-handler" value="<?=ADMIN?>/upload_ajax.php" />
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

<?php include('footer.php'); ?>