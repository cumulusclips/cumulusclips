<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/config/admin.bootstrap.php');

// Verify if user is logged in
$userService = new UserService();
$adminUser = $userService->loginCheck();
Functions::RedirectIf($adminUser, HOST . '/login/');
Functions::RedirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$commentMapper = new CommentMapper();
$commentService = new CommentService();
$userMapper = new UserMapper();
$videoMapper = new VideoMapper();
$videoService = new VideoService();
$records_per_page = 9;
$url = ADMIN . '/comments.php';
$query_string = array();
$message = null;
$sub_header = null;



### Handle "Delete" action
if (!empty ($_GET['delete']) && is_numeric ($_GET['delete']) && $_GET['delete'] > 0) {

    // Validate id
    $comment = $commentMapper->getCommentById($_GET['delete']);
    if ($comment) {
        $commentService->delete($comment);
        $message = 'Comment has been deleted';
        $message_type = 'success';
    }

}


### Handle "Approve" action
else if (!empty ($_GET['approve']) && is_numeric ($_GET['approve']) && $_GET['approve'] > 0) {

    // Validate id
    $comment = $commentMapper->getCommentById($_GET['approve']);
    if ($comment) {
        $commentService->approve($comment, 'approve');
        $message = 'Comment has been approved';
        $message_type = 'success';
    }

}


### Handle "Unban" action
else if (!empty ($_GET['unban']) && is_numeric ($_GET['unban']) && $_GET['unban'] > 0) {

    // Validate id
    $comment = $commentMapper->getCommentById($_GET['unban']);
    if ($comment) {
        $comment->approve($comment, 'approve');
        $message = 'Comment has been unbanned';
        $message_type = 'success';
    }

}


### Handle "Ban" action
else if (!empty ($_GET['ban']) && is_numeric ($_GET['ban']) && $_GET['ban'] > 0) {

    // Validate id
    $comment = $commentMapper->getCommentById($_GET['ban']);
    if ($comment) {
        $comment->status = 'banned';
        $commentMapper->save($comment);
        $flagService = new FlagService();
        $flagService->flagDecision($comment, true);
        $message = 'Comment has been banned';
        $message_type = 'success';
    }

}




### Determine which type (status) of record to display
$status = (!empty ($_GET['status'])) ? $_GET['status'] : 'approved';
switch ($status) {

    case 'pending':
        $query_string['status'] = 'pending';
        $header = 'Pending Comments';
        $page_title = 'Pending Comments';
        break;
    case 'banned':
        $query_string['status'] = 'banned';
        $header = 'Banned Comments';
        $page_title = 'Banned Comments';
        break;
    default:
        $status = 'approved';
        $header = 'Approved Comments';
        $page_title = 'Approved Comments';
        break;

}
$query = "SELECT comment_id FROM " . DB_PREFIX . "comments WHERE status = :status";
$queryParams = array(':status' => $status);




### Handle Search Records Form
if (isset ($_POST['search_submitted'])&& !empty ($_POST['search'])) {

    $like = trim ($_POST['search']);
    $query_string['search'] = $like;
    $query .= " AND comments LIKE :like";
    $queryParams[':like'] = "%$like%";
    $sub_header = "Search Results for: <em>$like</em>";

} else if (!empty ($_GET['search'])) {

    $like = trim ($_GET['search']);
    $query_string['search'] = $like;
    $query .= " AND comments LIKE :like";
    $queryParams[':like'] = "%$like%";
    $sub_header = "Search Results for: <em>$like</em>";

}




// Retrieve total count
$query .= " ORDER BY comment_id DESC";
$db->Query ($query, $queryParams);
$total = $db->rowCount();

// Initialize pagination
$url .= (!empty ($query_string)) ? '?' . http_build_query($query_string) : '';
$pagination = new Pagination ($url, $total, $records_per_page, false);
$start_record = $pagination->GetStartRecord();
$_SESSION['list_page'] = $pagination->GetURL();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$commentResults = $db->fetchAll($query, $queryParams);
$commentList = $commentMapper->getCommentsFromList(
    Functions::arrayColumn($commentResults, 'comment_id')
);


// Output Header
include ('header.php');

?>

<div id="comments">

    <h1><?=$header?></h1>
    <?php if ($sub_header): ?>
    <h3><?=$sub_header?></h3>
    <?php endif; ?>


    <?php if ($message): ?>
    <div class="message <?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <div id="browse-header">
        <div class="jump">
            Jump To:
            <select name="status" data-jump="<?=ADMIN?>/comments.php">
                <option <?=(isset($status) && $status == 'approved') ? 'selected="selected"' : ''?>value="approved">Approved</option>
                <option <?=(isset($status) && $status == 'pending') ? 'selected="selected"' : ''?>value="pending">Pending</option>
                <option <?=(isset($status) && $status == 'banned') ? 'selected="selected"' : ''?>value="banned">Banned</option>
            </select>
        </div>

        <div class="search">
            <form method="POST" action="<?=ADMIN?>/comments.php?status=<?=$status?>">
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
                        <td class="large">Poster</td>
                        <td class="large">Comments</td>
                        <td class="large">Video</td>
                        <td class="large">Date Posted</td>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($commentList as $comment): ?>

                    <?php $odd = empty ($odd) ? true : false; ?>
                    <?php $video = $videoMapper->getVideoById($comment->videoId); ?>
                    <?php $user = $userMapper->getUserById($comment->userId); ?>

                    <tr class="<?=$odd ? 'odd' : ''?>">
                        <td>
                            <?php $avatar = $commentService->getCommentAvatar($comment); ?>
                            <img src="<?=($avatar) ? $avatar : $config->theme_url_default . '/images/avatar.gif'?>" height="80" width="80" />
                            <p class="poster"><?=($comment->userId==0)?$comment->email:'<a href="' . HOST . '/members/' . $comment->name . '/">' . $comment->name . '</a>'?></p>
                        </td>
                        <td class="comments-text">
                            <?=Functions::CutOff (htmlspecialchars($comment->comments), 150)?>
                            <div class="record-actions invisible">
                                <a href="<?=ADMIN?>/comments_edit.php?id=<?=$comment->commentId?>">Edit</a>

                                <?php if ($status == 'approved'): ?>
                                    <a class="delete" href="<?=$pagination->GetURL('ban='.$comment->commentId)?>">Ban</a>
                                <?php elseif ($status == 'pending'): ?>
                                    <a class="approve" href="<?=$pagination->GetURL('approve='.$comment->commentId)?>">Approve</a>
                                <?php elseif ($status == 'banned'): ?>
                                    <a href="<?=$pagination->GetURL('unban='.$comment->commentId)?>">Unban</a>
                                <?php endif; ?>

                                <a class="delete confirm" href="<?=$pagination->GetURL('delete='.$comment->commentId)?>" data-confirm="You're about to delete this comment. This cannot be undone. Do you want to proceed?">Delete</a>
                            </div>
                        </td>
                        <td><a href="<?=$videoService->getUrl($video)?>/"><?=$video->title?></a></td>
                        <td><?=Functions::DateFormat('m/d/Y',$comment->dateCreated)?></td>
                    </tr>

                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?=$pagination->paginate()?>

    <?php else: ?>
        <div class="block"><strong>No comments found</strong></div>
    <?php endif; ?>

</div>

<?php include ('footer.php'); ?>