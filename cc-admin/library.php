<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$authService->enforceTimeout(true);

// Verify user can access admin panel
$userService = new \UserService();
Functions::redirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$fileMapper = new FileMapper();
$fileService = new FileService();
$page_title = 'File Library';
$records_per_page = 12;
$url = ADMIN . '/library.php';
$query_string = array();
$message = null;
$sub_header = null;
$queryParams = array(':type' => \FileMapper::TYPE_LIBRARY);

// Handle "Delete" file if requested
if (!empty($_GET['delete']) && is_numeric($_GET['delete'])) {

    // Validate file id
    $file = $fileMapper->getById($_GET['delete']);
    if ($file) {
        $fileService->delete($file);
        $message = 'File has been deleted';
        $message_type = 'alert-success';
    }
}

// Determine which type (status) of file to display
$query = "SELECT file_id "
    . " FROM " . DB_PREFIX . $fileMapper::TABLE
    . " WHERE type = :type";

// Handle search form
if (isset($_POST['search_submitted'])&& !empty($_POST['search'])) {
    $like = trim($_POST['search']);
    $query_string['search'] = $like;
    $query .= " AND name LIKE :like";
    $sub_header = "Search Results for: <em>$like</em>";
    $queryParams[':like'] = '%' . $like . '%';
} else if (!empty($_GET['search'])) {
    $like = trim($_GET['search']);
    $query_string['search'] = $like;
    $query .= " AND name LIKE :like";
    $sub_header = "Search Results for: <em>$like</em>";
    $queryParams[':like'] = '%' . $like . '%';
}

// Retrieve total count
$query .= ' ORDER BY ' . $fileMapper::KEY . ' DESC';
$db->fetchAll($query, $queryParams);
$total = $db->rowCount();

// Initialize pagination
$url .= (!empty($query_string)) ? '?' . http_build_query($query_string) : '';
$pagination = new Pagination($url, $total, $records_per_page, false);
$start_record = $pagination->getStartRecord();
$_SESSION['list_page'] = $pagination->getURL();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$fileResults = $db->fetchAll($query, $queryParams);
$fileList = $fileMapper->getfromList(
    Functions::arrayColumn($fileResults, $fileMapper::KEY)
);

// Output Header
$pageName = 'library';
include('header.php');

?>

<h1>File Library</h1>

<?php if ($sub_header): ?>
    <h3><?=$sub_header?></h3>
<?php endif; ?>

<?php if ($message): ?>
<div class="alert <?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<div class="filters">

    <a href="<?=ADMIN?>/library_add.php" class="button">Add New File</a>

    <div class="search">
        <form method="POST" action="<?=ADMIN?>/library.php">
            <input type="hidden" name="search_submitted" value="true" />
            <input class="form-control" type="text" name="search" value="" />
            <input type="submit" name="submit" class="button" value="Search" />
        </form>
    </div>
</div>

<?php if ($total > 0): ?>

    <table class="table table-striped">
        <thead>
            <tr>
                <th class="name">Name</th>
                <th class="url">URL</th>
                <th>Filesize</th>
                <th>Upload Date</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($fileList as $file): ?>

            <tr>
                <td class="name">
                    <a href="<?=ADMIN?>/files_edit.php?id=<?=$file->fileId?>" class="h3"><?=$file->name?></a><br />
                    <div class="record-actions">
                        <a href="<?=ADMIN?>/library_edit.php?id=<?=$file->fileId?>">Edit</a>
                        <a href="<?=$fileService->getUrl($file)?>" target="_ccsite">View file</a>
                        <a class="delete confirm" href="<?=$pagination->getURL('delete='.$file->fileId)?>" data-confirm="You're about to delete this file. This cannot be undone. Do you want to proceed?">Delete</a>
                    </div>
                </td>
                <td class="url"><?=$fileService->getUrl($file)?></td>
                <td><?=Functions::formatBytes($file->filesize, 0)?></td>
                <td><?php echo \Functions::gmtToLocal($file->dateCreated, 'm/d/Y'); ?></td>
            </tr>

        <?php endforeach; ?>
        </tbody>
    </table>

<?php else: ?>
    <p>No files found</p>
<?php endif; ?>

<?php include('footer.php'); ?>