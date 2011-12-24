<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Page');
App::LoadClass ('Pagination');


// Establish page variables, objects, arrays, etc
Functions::RedirectIf ($logged_in = User::LoginCheck(), HOST . '/login/');
$admin = new User ($logged_in);
Functions::RedirectIf (User::CheckPermissions ('admin_panel', $admin), HOST . '/myaccount/');
$records_per_page = 9;
$url = ADMIN . '/pages.php';
$query_string = array();
$message = null;
$sub_header = null;



### Handle "Delete" record if requested
if (!empty ($_GET['delete']) && is_numeric ($_GET['delete'])) {

    // Validate id
    if (Page::Exist (array ('page_id' => $_GET['delete']))) {
        Page::Delete ($_GET['delete']);
        $message = 'Page has been deleted';
        $message_type = 'success';
    }

}





### Determine which type (status) of pages to display
$status = (!empty ($_GET['status'])) ? $_GET['status'] : 'published';
switch ($status) {

    case 'draft':
        $query_string['status'] = 'draft';
        $header = 'Draft Pages';
        $page_title = 'Draft Pages';
        break;
    default:
        $status = 'published';
        $header = 'Published Pages';
        $page_title = 'Published Pages';
        break;

}
$query = "SELECT page_id FROM " . DB_PREFIX . "pages WHERE status = '$status'";



// Handle Search Member Form
if (isset ($_POST['search_submitted'])&& !empty ($_POST['search'])) {

    $like = $db->Escape (trim ($_POST['search']));
    $query_string['search'] = $like;
    $query .= " AND title LIKE '%$like%' OR content LIKE '%$like%'";
    $sub_header = "Search Results for: <em>$like</em>";

} else if (!empty ($_GET['search'])) {

    $like = $db->Escape (trim ($_GET['search']));
    $query_string['search'] = $like;
    $query .= " AND title LIKE '%$like%' OR content LIKE '%$like%'";
    $sub_header = "Search Results for: <em>$like</em>";

}



// Retrieve total count
$query .= " ORDER BY page_id DESC";
$result_count = $db->Query ($query);
$total = $db->Count ($result_count);

// Initialize pagination
$url .= (!empty ($query_string)) ? '?' . http_build_query($query_string) : '';
$pagination = new Pagination ($url, $total, $records_per_page, false);
$start_record = $pagination->GetStartRecord();
$_SESSION['list_page'] = $pagination->GetURL();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$result = $db->Query ($query);


// Output Header
include ('header.php');

?>

<div id="pages">

    <h1><?=$header?></h1>
    <?php if ($sub_header): ?>
    <h3><?=$sub_header?></h3>
    <?php endif; ?>


    <?php if ($message): ?>
    <div class="<?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <div id="browse-header">

        <div class="jump">
            Jump To:
            <select name="status" data-jump="<?=ADMIN?>/pages.php">
                <option <?=(isset($status) && $status == 'published') ? 'selected="selected"' : ''?>value="published">Published</option>
                <option <?=(isset($status) && $status == 'draft') ? 'selected="selected"' : ''?>value="draft">Draft</option>
            </select>
        </div>

        <a class="button add" href="<?=ADMIN?>/pages_add.php">Add New</a>

        <div class="search">
            <form method="POST" action="<?=ADMIN?>/pages.php?status=<?=$status?>">
                <input type="hidden" name="search_submitted" value="true" />
                <input type="text" name="search" value="" />&nbsp;
                <input type="submit" name="submit" class="button" value="Search" />
            </form>
        </div>

    </div>

    <?php if ($total > 0): ?>

        <div class="block list">
            <table>
                <thead>
                    <tr>
                        <td class="large">Title</td>
                        <td class="large">Status</td>
                        <td class="large">Date Created</td>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $db->FetchObj ($result)): ?>

                    <?php $odd = empty ($odd) ? true : false; ?>
                    <?php $page = new Page ($row->page_id); ?>

                    <tr class="<?=$odd ? 'odd' : ''?>">
                        <td>
                            <a href="<?=ADMIN?>/pages_edit.php?id=<?=$page->page_id?>" class="large"><?=$page->title?></a><br />
                            <div class="record-actions invisible">
                                <a href="<?=HOST?>/page/?preview=<?=$page->page_id?>" target="_ccsite">Preview</a>
                                <a href="<?=ADMIN?>/pages_edit.php?id=<?=$page->page_id?>">Edit</a>
                                <a class="delete confirm" href="<?=$pagination->GetURL('delete='.$page->page_id)?>" data-confirm="You are about to delete this page, this cannot be undone. Are you sure you want to do this?">Delete</a>
                            </div>
                        </td>
                        <td><?=($page->status == 'published') ? 'Published' : 'Draft'?></td>
                        <td><?=Functions::DateFormat('m/d/Y',$page->date_created)?></td>
                    </tr>

                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <?=$pagination->paginate()?>

    <?php else: ?>
        <div class="block"><strong>No pages found</strong></div>
    <?php endif; ?>

</div>

<?php include ('footer.php'); ?>