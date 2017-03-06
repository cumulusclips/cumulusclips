<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify user can access admin panel
$userService = new \UserService();
Functions::RedirectIf($userService->checkPermissions('manage_settings', $adminUser), HOST . '/account/');

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

    // Validate form nonce token and submission speed
    if (
        !empty($_POST['nonce'])
        && !empty($_SESSION['formNonce'])
        && !empty($_SESSION['formTime'])
        && $_POST['nonce'] == $_SESSION['formNonce']
        && time() - $_SESSION['formTime'] >= 2
    ) {
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
                $pluginName = array_pop($extractionDirectoryContents);
                if (file_exists(DOC_ROOT . '/cc-content/plugins/' . $pluginName)) {
                    throw new Exception("Plugin cannot be added. It conflicts with another plugin.");
                }

                // Copy plugin contents to plugins dir
                Filesystem::copyDir($extractionDirectory . '/' . $pluginName, DOC_ROOT . '/cc-content/plugins/' . $pluginName);

                // Validate Plugin
                if (!Plugin::isPluginValid($pluginName)) {
                    throw new Exception("Plugin contains errors. Please report this to it's developer");
                }

                // Clean up
                Filesystem::delete($extractionDirectory);

                // Display success message
                $plugin = Plugin::getPlugin($pluginName);
                $message = $plugin->name . ' has been uploaded and is available for use.';
                $message_type = 'alert-success';

            } catch (Exception $e) {
                $message = $e->getMessage();
                $message_type = 'alert-danger';
            }

        } else {
            $message = 'Invalid file upload';
            $message_type = 'alert-danger';
        }

    } else {
        $message = 'Expired or invalid session';
        $message_type = 'alert-danger';
    }
}

// Generate new form nonce
$formNonce = md5(uniqid(rand(), true));
$_SESSION['formNonce'] = $formNonce;
$_SESSION['formTime'] = time();

// Output Header
$pageName = 'plugins-add';
include('header.php');

?>

<h1>Add New Plugin</h1>

<div class="alert <?=$message_type?>"><?=$message?></div>

<p>If you have a plugin in .zip format use this form
to upload and add it to the system.</p>

<form action="<?php echo ADMIN; ?>/plugins_add.php" method="post">

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

    <input type="hidden" value="yes" name="submitted" />
    <input type="hidden" name="nonce" value="<?=$formNonce?>" />

</form>

<?php include('footer.php'); ?>