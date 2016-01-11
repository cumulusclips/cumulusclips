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
$fileMapper = new FileMapper();
$fileService = new FileService();
$file = new File();
$page_title = 'Add New File';
$data = array();
$errors = array();
$message = null;
$message_type = null;
$admin_js[] = ADMIN . '/extras/fileupload/fileupload.jquery-ui.widget.js';
$admin_js[] = ADMIN . '/extras/fileupload/fileupload.iframe-transport.js';
$admin_js[] = ADMIN . '/extras/fileupload/fileupload.plugin.js';
$admin_js[] = ADMIN . '/js/fileupload.js';
$tempFile = null;
$uploadMessage = null;
$originalName = null;
$filesize = null;

// Handle form if submitted
if (isset($_POST['submitted'])) {

    // Validate file upload
    if (!empty($_POST['original-name']) && !empty($_POST['filesize']) && !empty($_POST['temp-file']) && file_exists($_POST['temp-file'])) {
        $filesize = $_POST['filesize'];
        $tempFile = $_POST['temp-file'];
        $originalName = trim($_POST['original-name']);
        $uploadMessage = $originalName . ' - has been uploaded.';
    } else {
        $errors['file'] = 'Invalid file upload';
    }

    // Validate title
    if (!empty($_POST['title'])) {
        $file->title = trim($_POST['title']);
    } else {
        $errors['title'] = 'Invalid title';
    }

    // Validate description
    if (!empty($_POST['description'])) {
        $file->description = trim($_POST['description']);
    } else {
        $file->description = null;
    }

    // Update file if no errors were made
    if (empty($errors)) {

        // Add remaining file information
        $file->filename = $fileService->generateFilename();
        $file->userId = $adminUser->userId;
        $file->extension = Functions::getExtension($tempFile);
        $file->filesize = round($filesize/1000);

        try {
            // Move file to files directory
            Filesystem::rename($tempFile, UPLOAD_PATH . '/files/' . $file->filename . '.' . $file->extension);
        
            // Create record
            $fileId = $fileMapper->save($file);

            // Output message
            $tempFile = null;
            $uploadMessage = null;
            $originalName = null;
            $message = 'File has been created.';
            $message_type = 'alert-success';
            $file = null;
        } catch (Exception $exception) {
            $message = $exception->getMessage();
            $message_type = 'alert-danger';
        }

    } else {
        $message = 'The following errors were found. Please correct them and try again.';
        $message .= '<br /><br /> - ' . implode('<br /> - ', $errors);
        $message_type = 'alert-danger';
    }
}

// Output Header
$pageName = 'library-add';
include('header.php');

?>

<!--[if IE 9 ]> <meta name="ie9" content="true" /> <![endif]-->

<h1>Add New File</h1>

<div class="alert <?=$message_type?>"><?=$message?></div>

<form action="<?=ADMIN?>/library_add.php" method="post">

    <div class="form-group select-file <?=(isset ($errors['file'])) ? 'has-error' : ''?>">
        <label class="control-label">File:</label>
        <div class="button button-browse">
            <span>Browse</span>
            <input id="upload" type="file" name="upload" />
        </div>
        <input type="button" class="button button-upload" value="Upload" />
        <input type="hidden" name="upload-limit" value="<?=$config->fileSizeLimit?>" />
        <input type="hidden" name="filesize" value="<?=$filesize?>" />
        <input type="hidden" name="file-types" value="*" />
        <input type="hidden" name="upload-type" value="library" />
        <input type="hidden" name="original-name" value="<?=htmlspecialchars($originalName)?>" />
        <input type="hidden" name="temp-file" value="<?=$tempFile?>" />
        <input type="hidden" name="upload-handler" value="<?=ADMIN?>/upload_ajax.php" />
    </div>

    <?php $class = ($uploadMessage) ? 'show' : 'hidden'; ?>
    <div class="upload-complete <?=$class?>"><?=$uploadMessage?></div>

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

    <div class="form-group <?=(isset($errors['title'])) ? 'has-error' : ''?>">
        <label class="control-label">Title:</label>
        <input class="form-control" type="text" name="title" value="<?=(!empty($file->title)) ? htmlspecialchars($file->title) : ''?>" />
    </div>

    <div class="form-group">
        <label class="control-label">Description:</label>
        <textarea rows="7" cols="50" class="form-control" name="description"><?=(!empty($file->description)) ? htmlspecialchars($file->description) : ''?></textarea>
    </div>

    <input type="hidden" name="submitted" value="TRUE" />
    <input type="submit" class="button" value="Add File" />

</form>

<?php include('footer.php'); ?>