<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$userService = new UserService();
$adminUser = $userService->loginCheck();
Functions::RedirectIf($adminUser, HOST . '/login/');
Functions::RedirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$fileMapper = new FileMapper();
$fileService = new FileService();
$page_title = 'Edit File';
$pageName = 'library-edit';
$data = array();
$errors = array();
$message = null;

// Build return to list link
if (!empty ($_SESSION['list_page'])) {
    $list_page = $_SESSION['list_page'];
} else {
    $list_page = ADMIN . '/library.php';
}

// Verify a file was provided
if (!empty($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0) {

    // Retrieve file information
    $file = $fileMapper->getById($_GET['id']);
    if (!$file) {
        header('Location: ' . ADMIN . '/library.php');
        exit();
    }
} else {
    header('Location: ' . ADMIN . '/library.php');
    exit();
}

// Handle form if submitted
if (isset($_POST['submitted'])) {

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
        $fileMapper->save($file);
        $message = 'File has been updated.';
        $message_type = 'alert-success';
    } else {
        $message = 'The following errors were found. Please correct them and try again.';
        $message .= '<br /><br /> - ' . implode ('<br /> - ', $errors);
        $message_type = 'alert-danger';
    }
}

// Output Header
include ('header.php');

?>

<h1>Edit File</h1>

<?php if ($message): ?>
<div class="alert <?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<p><a href="<?=$list_page?>">Return to previous screen</a></p>

<form action="<?=ADMIN?>/library_edit.php?id=<?=$file->fileId?>" method="post">

    <div class="form-group <?=(isset ($errors['title'])) ? 'has-error' : ''?>">
        <label class="control-label">Title:</label>
        <input class="form-control" type="text" name="title" value="<?=htmlspecialchars($file->title)?>" />
    </div>

    <div class="form-group <?=(isset ($errors['description'])) ? 'has-error' : ''?>">
        <label class="control-label">Description:</label>
        <textarea rows="7" cols="50" class="form-control" name="description"><?=htmlspecialchars($file->description)?></textarea>
    </div>

    <input type="hidden" name="submitted" value="TRUE" />
    <input type="submit" class="button" value="Update File" />
</form>

<?php include ('footer.php'); ?>