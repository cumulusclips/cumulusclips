<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$userService = new UserService();
$adminUser = $userService->loginCheck();
Functions::redirectIf($adminUser, HOST . '/login/');
Functions::redirectIf($userService->checkPermissions('manage_settings', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$page_title = 'Add New Plugin';
$message = null;
$message_type = null;
$admin_js[] = ADMIN . '/extras/fileupload/fileupload.jquery-ui.widget.js';
$admin_js[] = ADMIN . '/extras/fileupload/fileupload.iframe-transport.js';
$admin_js[] = ADMIN . '/extras/fileupload/fileupload.plugin.js';
$admin_js[] = ADMIN . '/js/fileupload.js';

// Handle Upload Form
if (isset($_POST['submitted'])) {

    // Validate file upload
    if (!empty($_POST['temp-file']) && file_exists($_POST['temp-file'])) {
        
        try {
            // Create extraction directory
            $tempFile = $_POST['temp-file'];
            $extractionDirectory = UPLOAD_PATH . '/temp/' .  basename($tempFile, '.zip');
            Filesystem::createDir($extractionDirectory);
            Filesystem::setPermissions($extractionDirectory, 0777);
            
            // Move zip to extraction directory
            Filesystem::rename($tempFile, $extractionDirectory . '/addon.zip');

            // Extract theme
            Filesystem::extract($extractionDirectory . '/addon.zip');

            // Check for duplicates
            $extractionDirectoryContents = array_diff(scandir($extractionDirectory), array('.', '..', 'addon.zip'));
            $themeName = array_pop($extractionDirectoryContents);
            if (file_exists(DOC_ROOT . '/cc-content/themes/' . $themeName)) {
                throw new Exception("Theme cannot be added. It conflicts with another theme.");
            }

            // Copy theme contents to themes dir
            Filesystem::copyDir($extractionDirectory . '/' . $themeName, THEMES_DIR . '/' . $themeName);

            // Validate theme
            if (!Functions::validTheme($themeName)) {
                throw new Exception("Theme contains errors. Please report this to it's developer");
            }

            // Clean up
            Filesystem::delete($extractionDirectory);

            // Display success message
            $xml = simplexml_load_file(THEMES_DIR . '/' . $themeName . '/theme.xml');
            $message = $xml->name . ' has been added.';
            $message_type = 'alert-success';

        } catch (Exception $e) {
            $message = $e->getMessage();
            $message_type = 'alert-danger';
        }

    } else {
        $message = 'Invalid file upload';
        $message_type = 'alert-danger';
    }
}

// Output Header
$pageName = 'themes-add';
include('header.php');

?>

<!--[if IE 9 ]> <meta name="ie9" content="true" /> <![endif]-->

<h1>Add New Theme</h1>

<div class="alert <?=$message_type?>"><?=$message?></div>

<p class="row-shift">If you have a theme in .zip format use this form
to upload and add it to the system.</p>

<form action="<?=ADMIN?>/themes_add.php" method="post">

    <div class="form-group select-file">
        <label>Theme Zip File:</label>
        <div class="button button-browse">
            <span>Browse</span>
            <input id="upload" type="file" name="upload" />
        </div>
        <input type="button" class="button button-upload" value="Upload" />
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

<?php include('footer.php'); ?>