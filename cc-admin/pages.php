<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$authService->enforceTimeout(true);

// Verify user can access admin panel
$userService = new \UserService();
Functions::RedirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$pageMapper = new PageMapper();
$pageService = new PageService();
$records_per_page = 9;
$url = ADMIN . '/pages.php';
$query_string = array();
$message = null;
$sub_header = null;



### Handle "Delete" record if requested
if (!empty ($_GET['delete']) && is_numeric ($_GET['delete'])) {

    // Validate id
    $page = $pageMapper->getPageById($_GET['delete']);
    if ($page) {
        $pageService->delete($page);
        $message = 'Page has been deleted';
        $message_type = 'alert-success';
    }

}





### Determine which type (status) of pages to display
$status = (!empty ($_GET['status'])) ? $_GET['status'] : 'published';
switch ($status) {

    case 'draft':
        $query_string['status'] = 'draft';
        $header = 'Draft Pages';
        $page_title = 'Draft Pages';
        $statusText = 'Drafts';
        break;
    default:
        $status = 'published';
        $header = 'Published Pages';
        $page_title = 'Published Pages';
        $statusText = 'Published';
        break;

}
$query = "SELECT page_id FROM " . DB_PREFIX . "pages WHERE status = '$status'";
$queryParams = array();



// Handle Search Member Form
if (isset ($_POST['search_submitted'])&& !empty ($_POST['search'])) {

    $like = trim ($_POST['search']);
    $query_string['search'] = $like;
    $query .= " AND (title LIKE :like OR content LIKE :like)";
    $sub_header = "Search Results for: <em>$like</em>";
    $queryParams[':like'] = "%$like%";

} else if (!empty ($_GET['search'])) {

    $like = trim ($_GET['search']);
    $query_string['search'] = $like;
    $query .= " AND (title LIKE :like OR content LIKE :like)";
    $sub_header = "Search Results for: <em>$like</em>";
    $queryParams[':like'] = "%$like%";

}



// Retrieve total count
$query .= " ORDER BY page_id DESC";
$db->fetchAll ($query, $queryParams);
$total = $db->rowCount();

// Initialize pagination
$url .= (!empty ($query_string)) ? '?' . http_build_query($query_string) : '';
$pagination = new Pagination ($url, $total, $records_per_page, false);
$start_record = $pagination->GetStartRecord();
$_SESSION['list_page'] = $pagination->GetURL();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$resultPages = $db->fetchAll ($query, $queryParams);
$pageList = $pageMapper->getPagesFromList(
    Functions::arrayColumn($resultPages, 'page_id')
);


// Output Header
$pageName = 'pages';
include ('header.php');

?>

<h1><?=$header?></h1>
<?php if ($sub_header): ?>
<h3><?=$sub_header?></h3>
<?php endif; ?>


<?php if ($message): ?>
<div class="alert <?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<div class="filters">
    <div class="jump">
        Jump To:

        <div class="dropdown">
          <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
            <?=$statusText?>
            <span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
            <li><a tabindex="-1" href="<?=ADMIN?>/pages.php?status=published">Published</a></li>
            <li><a tabindex="-1" href="<?=ADMIN?>/pages.php?status=draft">Draft</a></li>
          </ul>
        </div>
        <a class="button add" href="<?=ADMIN?>/pages_add.php">Add New</a>
    </div>

    <div class="search">
        <form method="POST" action="<?=ADMIN?>/pages.php?status=<?=$status?>">
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
                <th>Title</th>
                <th>Status</th>
                <th>Date Created</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($pageList as $page): ?>

            <tr>
                <td>
                    <a href="<?=ADMIN?>/pages_edit.php?id=<?=$page->pageId?>" class="h3"><?=$page->title?></a><br />
                    <div class="record-actions invisible">
                        <a href="<?=HOST?>/page/?preview=<?=$page->pageId?>" target="_ccsite">Preview</a>
                        <a href="<?=ADMIN?>/pages_edit.php?id=<?=$page->pageId?>">Edit</a>
                        <a class="delete confirm" href="<?=$pagination->GetURL('delete='.$page->pageId)?>" data-confirm="You are about to delete this page, this cannot be undone. Are you sure you want to do this?">Delete</a>
                    </div>
                </td>
                <td><?=($page->status == 'published') ? 'Published' : 'Draft'?></td>
                <td><?=date('m/d/Y', strtotime($page->dateCreated))?></td>
            </tr>

        <?php endforeach; ?>
        </tbody>
    </table>

<?php else: ?>
    <p>No pages found</p>
<?php endif; ?>

<?php include ('footer.php'); ?>