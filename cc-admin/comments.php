<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Comment');
App::LoadClass ('Video');
App::LoadClass ('Flag');
App::LoadClass ('Pagination');


// Establish page variables, objects, arrays, etc
Plugin::Trigger ('admin.members.start');
Functions::RedirectIf ($logged_in = User::LoginCheck(), HOST . '/login/');
$admin = new User ($logged_in);
Functions::RedirectIf (User::CheckPermissions ('admin_panel', $admin), HOST . '/myaccount/');
$records_per_page = 9;
$url = ADMIN . '/comments.php';
$query_string = array();
$message = null;
$sub_header = null;



### Handle "Delete" action
if (!empty ($_GET['delete']) && is_numeric ($_GET['delete'])) {

    // Validate id
    if (Comment::Exist (array ('comment_id' => $_GET['delete']))) {
        Comment::Delete ($_GET['delete']);
        $message = 'Comment has been deleted';
        $message_type = 'success';
    }

}


### Handle "Approve" action
else if (!empty ($_GET['approve']) && is_numeric ($_GET['approve'])) {

    // Validate id
    $comment = new Comment ($_GET['approve']);
    if ($comment->found) {
        $comment->Approve ('approve');
        $message = 'Comment has been approved';
        $message_type = 'success';
    }

}


### Handle "Unban" action
else if (!empty ($_GET['unban']) && is_numeric ($_GET['unban'])) {

    // Validate id
    $comment = new Comment ($_GET['unban']);
    if ($comment->found) {
        $comment->Approve ('approve');
        $message = 'Comment has been unbanned';
        $message_type = 'success';
    }

}


### Handle "Ban" action
else if (!empty ($_GET['ban']) && is_numeric ($_GET['ban'])) {

    // Validate id
    $comment = new Comment ($_GET['ban']);
    if ($comment->found) {
        $comment->Update (array ('status' => 'banned'));
        Flag::FlagDecision ($comment->comment_id, 'comment', true);
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
$query = "SELECT comment_id FROM " . DB_PREFIX . "comments WHERE status = '$status'";




### Handle Search Records Form
if (isset ($_POST['search_submitted'])&& !empty ($_POST['search'])) {

    $like = $db->Escape (trim ($_POST['search']));
    $query_string['search'] = $like;
    $query .= " AND comments LIKE '%$like%'";
    $sub_header = "Search Results for: <em>$like</em>";

} else if (!empty ($_GET['search'])) {

    $like = $db->Escape (trim ($_GET['search']));
    $query_string['search'] = $like;
    $query .= " AND comments LIKE '%$like%'";
    $sub_header = "Search Results for: <em>$like</em>";

}




// Retrieve total count
$query .= " ORDER BY comment_id DESC";
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

<div id="comments">

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
                <?php while ($row = $db->FetchObj ($result)): ?>

                    <?php $odd = empty ($odd) ? true : false; ?>
                    <?php $comment = new Comment ($row->comment_id); ?>
                    <?php $video = new Video ($comment->video_id); ?>

                    <tr class="<?=$odd ? 'odd' : ''?>">
                        <td>
                            <img src="<?=$comment->avatar_url?>" height="80" width="80" />
                            <p class="poster"><?=($comment->user_id==0)?$comment->email:'<a href="' . HOST . '/members/' . $comment->name . '/">' . $comment->name . '</a>'?></p>
                        </td>
                        <td class="comments-text">
                            <?=Functions::CutOff ($comment->comments_display, 150)?>
                            <div class="record-actions invisible">
                                <a href="<?=ADMIN?>/comments_edit.php?id=<?=$comment->comment_id?>">Edit</a>

                                <?php if ($status == 'approved'): ?>
                                    <a class="delete" href="<?=$pagination->GetURL('ban='.$comment->comment_id)?>">Ban</a>
                                <?php elseif ($status == 'pending'): ?>
                                    <a class="approve" href="<?=$pagination->GetURL('approve='.$comment->comment_id)?>">Approve</a>
                                <?php elseif ($status == 'banned'): ?>
                                    <a href="<?=$pagination->GetURL('unban='.$comment->comment_id)?>">Unban</a>
                                <?php endif; ?>

                                <a class="delete confirm" href="<?=$pagination->GetURL('delete='.$comment->comment_id)?>" data-confirm="You're about to delete this comment. This cannot be undone. Do you want to proceed?">Delete</a>
                            </div>
                        </td>
                        <td><a href="<?=$video->url?>/"><?=$video->title?></a></td>
                        <td><?=$comment->date_created?></td>
                    </tr>

                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <?=$pagination->paginate()?>

    <?php else: ?>
        <div class="block"><strong>No comments found</strong></div>
    <?php endif; ?>

</div>

<?php include ('footer.php'); ?>