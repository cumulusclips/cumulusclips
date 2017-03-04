<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify user can access admin panel
$userService = new \UserService();
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
$prepopulate = null;

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
        // Validate file upload
        if (
            !empty($_POST['upload']['original-name'])
            && !empty($_POST['upload']['original-size'])
            && !empty($_POST['upload']['temp'])
            && \App::isValidUpload($_POST['upload']['temp'], $adminUser, 'library')
        ) {
            $prepopulate = urlencode(json_encode(array(
                'path' => $_POST['upload']['temp'],
                'name' => trim($_POST['upload']['original-name']),
                'size' => trim($_POST['upload']['original-size'])
            )));
        } else {
            $errors['file'] = 'Invalid file upload';
        }

        // Validate name
        if (!empty($_POST['name'])) {
            $file->name = trim($_POST['name']);
        } else {
            $errors['name'] = 'Invalid name';
        }

        // Update file if no errors were made
        if (empty($errors)) {

            // Add remaining file information
            $file->filename = $fileService->generateFilename();
            $file->userId = $adminUser->userId;
            $file->extension = Functions::getExtension($_POST['upload']['temp']);
            $file->filesize = filesize($_POST['upload']['temp']);
            $file->type = \FileMapper::TYPE_LIBRARY;

            try {
                // Move file to files directory
                Filesystem::rename($_POST['upload']['temp'], UPLOAD_PATH . '/files/library/' . $file->filename . '.' . $file->extension);

                // Create record
                $fileId = $fileMapper->save($file);

                // Output message
                $prepopulate = null;
                $file = null;
                $message = 'File has been created.';
                $message_type = 'alert-success';
            } catch (Exception $exception) {
                $message = $exception->getMessage();
                $message_type = 'alert-danger';
            }

        } else {
            $message = 'The following errors were found. Please correct them and try again.';
            $message .= '<br /><br /> - ' . implode('<br /> - ', $errors);
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
$pageName = 'library-add';
include('header.php');

?>

<h1>Add New File</h1>

<div class="alert <?=$message_type?>"><?=$message?></div>

<form action="<?php echo ADMIN; ?>/library_add.php" method="post">

    <div class="form-group <?=(isset ($errors['file'])) ? 'has-error' : ''?>">
        <label class="control-label">File:</label>
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

    <div class="form-group <?=(isset($errors['name'])) ? 'has-error' : ''?>">
        <label class="control-label">Name:</label>
        <input class="form-control" type="text" name="name" value="<?=(!empty($file->name)) ? htmlspecialchars($file->name) : ''?>" />
    </div>

    <input type="hidden" value="yes" name="submitted" />
    <input type="hidden" name="nonce" value="<?=$formNonce?>" />
    <input type="submit" class="button" value="Add File" />

</form>

<?php include('footer.php'); ?>