<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$userService = new UserService();
$adminUser = $userService->loginCheck();
Functions::RedirectIf($adminUser, HOST . '/login/');
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
$uploadMessage = null;

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

    // Validate meta data file upload
    if (!empty($_POST['temp-file'])) {
        if (file_exists($_POST['temp-file'])) {
            $tempFile = $_POST['temp-file'];
            $uploadMessage = 'Meta data file has been uploaded.';
        } else {
            $errors['meta'] = 'Invalid meta data upload';
        }
    }

    try {

        $jobId = \ImportManager::createImport($adminUser, $tempFile);
        \Filesystem::delete($tempFile);

        // Output message
        $tempFile = null;
        $uploadMessage = null;
        $message = 'Import job (' . $jobId . ') has been created.';
        $message_type = 'alert-success';

    } catch (Exception $exception) {
        $message = $exception->getMessage();
        $message_type = 'alert-danger';
    }
}

// Output Header
$pageName = 'videos-imports-create';
include('header.php');

?>

<!--[if IE 9 ]> <meta name="ie9" content="true" /> <![endif]-->

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

    <div class="form-group select-file <?=(isset ($errors['video'])) ? 'has-error' : ''?>">
        <label class="control-label">Meta Data XML (Optional):</label>
        <div class="button button-browse">
            <span>Browse</span>
            <input id="upload" type="file" name="upload" />
        </div>
        <input type="button" class="button button-upload" value="Upload" />
        <input type="hidden" name="upload-limit" value="<?=$config->fileSizeLimit?>" />
        <input type="hidden" name="file-types" value="*" />
        <input type="hidden" name="upload-type" value="library" />
        <input type="hidden" name="original-name" value="" />
        <input type="hidden" name="temp-file" value="<?=$tempFile?>" />
        <input type="hidden" name="upload-handler" value="<?=ADMIN?>/upload_ajax.php" />
    </div>

    <?php $style = ($uploadMessage) ? 'display: block;' : ''; ?>
    <div class="upload-complete" style="<?=$style?>"><?=$uploadMessage?></div>

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

    <p>Need a blank meta data XML template? You can <a href="<?php echo ADMIN; ?>/videos_imports_create.php?download">download one here</a>.</p>

    <input type="hidden" name="submitted" value="TRUE" />
    <input type="submit" class="button" value="Begin Import" />

</form>

<?php include('footer.php'); ?>