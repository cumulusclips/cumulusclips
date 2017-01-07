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
    if (
        !empty($_POST['upload']['temp'])
        && \App::isValidUpload($_POST['upload']['temp'], $adminUser, 'addon')
    ) {

        try {
            // Create extraction directory
            $extractionDirectory = UPLOAD_PATH . '/temp/' .  basename($_POST['upload']['temp'], '.zip');
            Filesystem::createDir($extractionDirectory);
            Filesystem::setPermissions($extractionDirectory, 0777);

            // Move zip to extraction directory
            Filesystem::rename($_POST['upload']['temp'], $extractionDirectory . '/addon.zip');

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

<h1>Add New Theme</h1>

<div class="alert <?=$message_type?>"><?=$message?></div>

<p class="row-shift">If you have a theme in .zip format use this form
to upload and add it to the system.</p>

<form action="<?php echo ADMIN; ?>/themes_add.php" method="post">

    <div class="form-group">
        <input
            class="uploader"
            type="file"
            name="upload"
            data-url="<?php echo BASE_URL; ?>/ajax/upload/"
            data-text="<?php echo Language::getText('browse_files_button'); ?>"
            data-limit="<?php echo $config->fileSizeLimit; ?>"
            data-extensions="<?php echo urlencode(json_encode(array('zip'))); ?>"
            data-type="addon"
            data-auto-submit="true"
        />
    </div>

    <input type="hidden" name="submitted" value="true" />

</form>

<?php include('footer.php'); ?>