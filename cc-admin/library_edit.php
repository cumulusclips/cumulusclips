<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$authService->enforceTimeout(true);

// Verify user can access admin panel
$userService = new \UserService();
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

    // Validate form nonce token and submission speed
    if (
        !empty($_POST['nonce'])
        && !empty($_SESSION['formNonce'])
        && !empty($_SESSION['formTime'])
        && $_POST['nonce'] == $_SESSION['formNonce']
        && time() - $_SESSION['formTime'] >= 2
    ) {
        // Validate name
        if (!empty($_POST['name'])) {
            $file->name = trim($_POST['name']);
        } else {
            $errors['name'] = 'Invalid name';
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
include ('header.php');

?>

<h1>Edit File</h1>

<?php if ($message): ?>
<div class="alert <?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<p><a href="<?=$list_page?>">Return to previous screen</a></p>

<form action="<?=ADMIN?>/library_edit.php?id=<?=$file->fileId?>" method="post">

    <div class="form-group <?=(isset ($errors['name'])) ? 'has-error' : ''?>">
        <label class="control-label">Name:</label>
        <input class="form-control" type="text" name="name" value="<?=htmlspecialchars($file->name)?>" />
    </div>

    <input type="hidden" value="yes" name="submitted" />
    <input type="hidden" name="nonce" value="<?=$formNonce?>" />
    <input type="submit" class="button" value="Update File" />
</form>

<?php include ('footer.php'); ?>