<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$authService->enforceTimeout(true);

// Verify user can access admin panel
$userService = new \UserService();
Functions::redirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$commentMapper = new CommentMapper();
$commentService = new CommentService();
$videoMapper = new VideoMapper();
$videoService = new VideoService();
$recordsPerPage = 9;
$url = ADMIN . '/comments.php';
$queryString = array();
$message = null;
$subHeader = null;

// Handle "Delete" action
if (!empty($_GET['delete']) && is_numeric($_GET['delete']) && $_GET['delete'] > 0) {

    // Validate id
    $comment = $commentMapper->getCommentById($_GET['delete']);
    if ($comment) {
        $commentService->delete($comment);
        $message = 'Comment has been deleted';
        $messageType = 'alert-success';
    }
}


// Handle "Approve" action
else if (!empty($_GET['approve']) && is_numeric($_GET['approve']) && $_GET['approve'] > 0) {

    // Validate id
    $comment = $commentMapper->getCommentById($_GET['approve']);
    if ($comment) {
        $commentService->approve($comment, 'approve');
        $message = 'Comment has been approved';
        $messageType = 'alert-success';
    }
}


// Handle "Unban" action
else if (!empty($_GET['unban']) && is_numeric($_GET['unban']) && $_GET['unban'] > 0) {

    // Validate id
    $comment = $commentMapper->getCommentById($_GET['unban']);
    if ($comment) {
        $commentService->approve($comment, 'approve');
        $message = 'Comment has been unbanned';
        $messageType = 'alert-success';
    }
}


// Handle "Ban" action
else if (!empty($_GET['ban']) && is_numeric($_GET['ban']) && $_GET['ban'] > 0) {

    // Validate id
    $comment = $commentMapper->getCommentById($_GET['ban']);
    if ($comment) {
        $comment->status = 'banned';
        $commentMapper->save($comment);
        $flagService = new FlagService();
        $flagService->flagDecision($comment, true);
        $message = 'Comment has been banned';
        $messageType = 'alert-success';
    }
}

// Determine which type (status) of record to display
$status = (!empty($_GET['status'])) ? $_GET['status'] : 'approved';
switch ($status) {
    case 'pending':
        $queryString['status'] = 'pending';
        $header = 'Pending Comments';
        $page_title = 'Pending Comments';
        $pageName = 'comments-pending';
        $statusText = 'Pending';
        break;
    case 'banned':
        $queryString['status'] = 'banned';
        $header = 'Banned Comments';
        $page_title = 'Banned Comments';
        $pageName = 'comments-banned';
        $statusText = 'Banned';
        break;
    default:
        $status = 'approved';
        $header = 'Approved Comments';
        $page_title = 'Approved Comments';
        $pageName = 'comments-approved';
        $statusText = 'Approved';
        break;
}
$query = "SELECT comment_id FROM " . DB_PREFIX . "comments WHERE status = :status";
$queryParams = array(':status' => $status);

// Handle Search Records Form
if (isset($_POST['search_submitted']) && !empty($_POST['search'])) {
    $like = trim($_POST['search']);
    $queryString['search'] = $like;
    $query .= " AND comments LIKE :like";
    $queryParams[':like'] = "%$like%";
    $subHeader = "Search Results for: <em>$like</em>";
} else if (!empty($_GET['search'])) {
    $like = trim($_GET['search']);
    $queryString['search'] = $like;
    $query .= " AND comments LIKE :like";
    $queryParams[':like'] = "%$like%";
    $subHeader = "Search Results for: <em>$like</em>";
}

// Retrieve total count
$query .= " ORDER BY comment_id DESC";
$db->query($query, $queryParams);
$total = $db->rowCount();

// Initialize pagination
$url .= (!empty($queryString)) ? '?' . http_build_query($queryString) : '';
$pagination = new Pagination($url, $total, $recordsPerPage, false);
$startRecord = $pagination->getStartRecord();
$_SESSION['list_page'] = $pagination->GetURL();

// Retrieve limited results
$query .= " LIMIT $startRecord, $recordsPerPage";
$commentResults = $db->fetchAll($query, $queryParams);
$commentList = $commentMapper->getCommentsFromList(
    Functions::arrayColumn($commentResults, 'comment_id')
);

// Output Header
include('header.php');

?>

<h1><?=$header?></h1>
<?php if ($subHeader): ?>
<h3><?=$subHeader?></h3>
<?php endif; ?>


<?php if ($message): ?>
<div class="alert <?=$messageType?>"><?=$message?></div>
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
            <li><a tabindex="-1" href="<?=ADMIN?>/comments.php?status=approved">Approved</a></li>
            <li><a tabindex="-1" href="<?=ADMIN?>/comments.php?status=pending">Pending</a></li>
            <li><a tabindex="-1" href="<?=ADMIN?>/comments.php?status=banned">Banned</a></li>
          </ul>
        </div>
    </div>

    <div class="search">
        <form method="POST" action="<?=ADMIN?>/comments.php?status=<?=$status?>">
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
                <th>Poster</th>
                <th>Comments</th>
                <th>Video</th>
                <th>Date Posted</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($commentList as $comment): ?>

            <?php $commentCard = $commentService->getCommentCard($comment); ?>
            <?php $video = $videoMapper->getVideoById($comment->videoId); ?>

            <tr>
                <td>
                    <img src="<?=($commentCard->avatar) ? $commentCard->avatar : HOST . '/cc-content/themes/default/images/avatar.gif'?>" height="80" width="80" />
                    <p class="poster"><a href="<?=HOST?>/members/<?=$commentCard->author->username?>/"><?=$commentCard->author->username?></a></p>
                </td>
                <td class="comments-text">
                    <?=Functions::cutOff(htmlspecialchars($commentCard->comment->comments), 150)?>
                    <div class="record-actions invisible">
                        <a href="<?=ADMIN?>/comments_edit.php?id=<?=$commentCard->comment->commentId?>">Edit</a>

                        <?php if ($status == 'approved'): ?>
                            <a class="delete" href="<?=$pagination->GetURL('ban='.$commentCard->comment->commentId)?>">Ban</a>
                        <?php elseif ($status == 'pending'): ?>
                            <a class="approve" href="<?=$pagination->GetURL('approve='.$commentCard->comment->commentId)?>">Approve</a>
                        <?php elseif ($status == 'banned'): ?>
                            <a href="<?=$pagination->GetURL('unban='.$commentCard->comment->commentId)?>">Unban</a>
                        <?php endif; ?>

                        <a class="delete confirm" href="<?=$pagination->GetURL('delete='.$commentCard->comment->commentId)?>" data-confirm="You're about to delete this comment. This cannot be undone. Do you want to proceed?">Delete</a>
                    </div>
                </td>
                <td><a href="<?=$videoService->getUrl($video)?>/"><?=$video->title?></a></td>
                <td><?=date('m/d/Y', strtotime($commentCard->comment->dateCreated))?></td>
            </tr>

        <?php endforeach; ?>
        </tbody>
    </table>

<?php else: ?>
    <p>No comments found</p>
<?php endif; ?>

<?php include('footer.php'); ?>