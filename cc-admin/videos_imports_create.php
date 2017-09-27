<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$authService->enforceTimeout(true);

// Verify user can access admin panel
$userService = new \UserService();
Functions::RedirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/account/');
App::enableUploadsCheck();

// Establish page variables, objects, arrays, etc
$page_title = 'Create New Video Import';
$message = null;
$message_type = null;
$admin_js[] = ADMIN . '/extras/fileupload/fileupload.jquery-ui.widget.js';
$admin_js[] = ADMIN . '/extras/fileupload/fileupload.iframe-transport.js';
$admin_js[] = ADMIN . '/extras/fileupload/fileupload.plugin.js';
$admin_js[] = ADMIN . '/js/fileupload.js';
$tempFile = null;
$prepopulate = null;
$errors = null;

// Handle request for downloading meta data template
if (isset($_GET['download'])) {

    $template = <<<XML

<?xml version="1.0" encoding="UTF-8"?>
<meta>

<!--
** NOTES **

 - Add one video element for each video you wish to provide meta data for
 - All values are case sensitive
 - Fields that are optional can be omitted
 - Fields:
    - filename: Required, Must match exactly the filename of the video as it exists in the /cc-content/uploads/import directory,
        including the extension, i.e. for /cc-contents/uploads/import/sample_video.mp4 value would be sample_video.mp4
    - title: Optional, the title for the video
    - description: Optional, the description for the video
    - tags: Optional, Tags for the video. Add a child "tag" element for each keyword you want to provide
    - category: Optional, the category to add the video to. Must match exactly a category name already in the system
-->

    <video>
        <filename></filename>
        <title></title>
        <description></description>
        <tags>
            <tag></tag>
            <tag></tag>
        </tags>
        <category></category>
    </video>

    <video>
        <filename></filename>
        <title></title>
        <description></description>
        <tags>
            <tag></tag>
            <tag></tag>
        </tags>
        <category></category>
    </video>

</meta>

XML;

    $template = trim($template);
    header('Content-Description: File Transfer');
    header('Content-Type: text/xml');
    header('Content-Disposition: attachment; filename="Meta Data Template.xml"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . strlen($template));
    echo $template;
    exit();
}

// Handle form if submitted
if (isset($_POST['submitted'])) {

    // Validate form nonce token and submission speed
    if (
        !empty($_POST['nonce'])
        && !empty($_SESSION['formNonce'])
        && !empty($_SESSION['formTime'])
        && $_POST['nonce'] == $_SESSION['formNonce']
        && time() - $_SESSION['formTime'] >= 2
    ) {
        // Validate meta data file upload
        if (!empty($_POST['upload']['temp'])) {
            if (
                !empty($_POST['upload']['original-name'])
                && !empty($_POST['upload']['original-size'])
                && \App::isValidUpload($_POST['upload']['temp'], $adminUser, 'library')
            ) {
                $tempFile = $_POST['upload']['temp'];
                $prepopulate = array(
                    'path' => $_POST['upload']['temp'],
                    'name' => trim($_POST['upload']['original-name']),
                    'size' => trim($_POST['upload']['original-size'])
                );
            } else {
                $errors = 'Invalid meta data upload';
            }
        }

        if (!$errors) {

            try {

                $jobId = \ImportManager::createImport($adminUser, $tempFile);
                \Filesystem::delete($tempFile);

                // Output message
                $tempFile = null;
                $prepopulate = null;
                $message = 'Import job (' . $jobId . ') has been created.';
                $message_type = 'alert-success';

            } catch (Exception $exception) {
                $message = $exception->getMessage();
                $message_type = 'alert-danger';
            }

        } else {
            $message = $errors;
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
$pageName = 'videos-imports-create';
include('header.php');

?>

<h1>Create New Video Import</h1>

<div class="alert <?=$message_type?>"><?=$message?></div>

<p><a href="<?php echo ADMIN; ?>/videos_imports.php">Return to previous screen</a></p>

<form action="<?=ADMIN?>/videos_imports_create.php" method="post">


    <p>Use this feature to bulk import multiple videos into your system. Upload the videos to the
        <em>/cc-content/uploads/import</em> directory first then submit the form below.</p>

    <h3>Video Meta Data</h3>

    <p>The video meta data (title, description, tags, category) will be derrived from the filename
        of each imported video. If you wish to customize the meta data for your videos prior to
        importing, you may do so by providing a meta data XML file below.</p>

    <p>Need a blank meta data XML template? You can <a href="<?php echo ADMIN; ?>/videos_imports_create.php?download">download one here</a>.</p>

    <div class="form-group <?=(isset ($errors['meta'])) ? 'has-error' : ''?>">
        <label class="control-label">Meta Data XML (Optional):</label>
        <input
            class="uploader"
            type="file"
            name="upload"
            data-url="<?php echo BASE_URL; ?>/ajax/upload/"
            data-text="<?php echo Language::getText('browse_files_button'); ?>"
            data-limit="<?php echo $config->fileSizeLimit; ?>"
            data-type="library"
            data-prepopulate="<?php echo urlencode(json_encode($prepopulate)); ?>"
        />
    </div>


    <input type="hidden" name="submitted" value="TRUE" />
    <input type="hidden" name="nonce" value="<?=$formNonce?>" />
    <input type="submit" class="button" value="Begin Import" />

</form>

<?php include('footer.php'); ?>