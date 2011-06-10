<?php

### Created on February 28, 2009
### Created by Miguel A. Hurtado
### This script displays the site homepage


// Include required files
include ('../cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');
App::LoadClass ('Comment');
App::LoadClass ('Flag');
App::LoadClass ('Pagination');


// Establish page variables, objects, arrays, etc
Plugin::Trigger ('admin.members.start');
//$logged_in = User::LoginCheck(HOST . '/login/');
//$admin = new User ($logged_in);
$records_per_page = 9;
$url = ADMIN . '/flags.php';
$query_string = array();
$message = null;
$sub_header = null;



// Verify content type was provided
if (empty ($_GET['type']) || !in_array ($_GET['type'], array ('video', 'member', 'comment'))) App::Throw404();
$type = $_GET['type'];




### Handle "Ban" record
if (!empty ($_GET['ban']) && is_numeric ($_GET['ban'])) {

    switch ($type) {

        case 'video':
            $video = new Video ($_GET['ban']);
            if ($video->found) {
                Flag::FlagDecision ($video->video_id, $type, true);
                $video->Update (array ('status' => 'banned'));
                $message = 'Video has been banned';
                $message_type = 'success';
            }
            break;

        case 'user':
            $user = new User ($_GET['ban']);
            if ($user->found) {
                Flag::FlagDecision ($user->user_id, $type, true);
                $user->UpdateContentStatus ('banned');
                $user->Update (array ('status' => 'banned'));
                $message = 'Member has been banned';
                $message_type = 'success';
            }
            break;

        case 'comment':
            $comment = new Comment ($_GET['ban']);
            if ($comment->found) {
                Flag::FlagDecision ($comment->comment_id, $type, true);
                $comment->Update (array ('status' => 'banned'));
                $message = 'Comment has been banned';
                $message_type = 'success';
            }
            break;

    }

}



### Handle "Dismiss" flags
else if (!empty ($_GET['dismiss']) && is_numeric ($_GET['dismiss'])) {
    Flag::FlagDecision ($_GET['dismiss'], $type, false);
    $message = 'Flags has been dismissed';
    $message_type = 'success';
}




### Determine which type (account status) of members to display
switch ($type) {

    case 'member':
        $query_string['type'] = 'member';
        $header = 'Flagged Members';
        $page_title = 'Flagged Members';
        break;
    case 'video':
        $query_string['type'] = 'video';
        $header = 'Flagged Videos';
        $page_title = 'Flagged Videos';
        break;
    case 'comment':
        $query_string['type'] = 'comment';
        $header = 'Flagged Comments';
        $page_title = 'Flagged Comments';
        break;

}
$query = "SELECT flag_id FROM " . DB_PREFIX . "flags WHERE status = 'pending' AND type = '$type'";



// Handle Search Member Form
if (isset ($_POST['search_submitted'])&& !empty ($_POST['search'])) {

    $like = $db->Escape (trim ($_POST['search']));
    $query_string['search'] = $like;
    $query .= " AND username LIKE '%$like%'";
    $sub_header = "Search Results for: <em>$like</em>";

} else if (!empty ($_GET['search'])) {

    $like = $db->Escape (trim ($_GET['search']));
    $query_string['search'] = $like;
    $query .= " AND username LIKE '%$like%'";
    $sub_header = "Search Results for: <em>$like</em>";

}



// Retrieve total count
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

<div id="flags">

    <h1><?=$header?></h1>

    <?php if ($message): ?>
    <div class="<?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <div id="browse-header">
        <div class="jump">
            Jump To:
            <select id="flag-type-select" name="type" onChange="window.location='<?=ADMIN?>/flags.php?type='+this.value;">
                <option <?=($type == 'video') ? 'selected="selected"' : ''?>value="video">Videos</option>
                <option <?=($type == 'member') ? 'selected="selected"' : ''?>value="member">Members</option>
                <option <?=($type == 'comment') ? 'selected="selected"' : ''?>value="comment">Comments</option>
            </select>
        </div>

    </div>

    <?php if ($total > 0): ?>

        <div class="block list">
            <table>
                <thead>
                    <tr>
                        <td class="large">Content</td>
                        <td class="large">Flagged By</td>
                        <td class="large">Date Flagged</td>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $db->FetchObj ($result)): ?>

                    <?php $odd = empty ($odd) ? true : false; ?>
                    <?php $flag = new Flag ($row->flag_id); ?>
                    <?php $user = new User ($flag->user_id); ?>

                    <tr class="<?=$odd ? 'odd' : ''?>">
                        <td>

                            <?php if ($type == 'member'): ?>

                                <?php $user = new User ($flag->id); ?>
                                <a href="<?=ADMIN?>/member_edit.php?id=<?=$user->user_id?>" class="large"><?=$user->username?></a>
                                <div class="record-actions invisible">
                                    <a href="<?=HOST?>/members/<?=$user->username?>/">View Profile</a>
                                    <a href="<?=ADMIN?>/member_edit.php?id=<?=$user->user_id?>">Edit</a>

                            <?php elseif ($type == 'video'): ?>

                                <?php $video = new Video ($flag->id); ?>
                                <a href="<?=ADMIN?>/video_edit.php?id=<?=$video->title?>" class="large"><?=$video->title?></a>
                                <div class="record-actions invisible">
                                    <a href="<?=HOST?>/videos/<?=$video->video_id?>/<?=$video->title?>/">Watch Video</a>
                                    <a href="<?=ADMIN?>/video_edit.php?id=<?=$video->video_id?>">Edit</a>

                            <?php elseif ($type == 'comment'): ?>

                                <?php $comment = new Comment ($flag->id); ?>
                                <?php $video = new Video ($comment->video_id); ?>

                                <?=$comment->comments_display?>
                                <div class="record-actions invisible">
                                    <a href="<?=ADMIN?>/comment_edit.php?id=<?=$comment->comment_id?>">Edit</a>

                            <?php endif; ?>

                                <a href="<?=$pagination->GetURL('dismiss='.$flag->id)?>">Dismiss Flag</a>
                                <a class="delete" href="<?=$pagination->GetURL('ban='.$flag->id)?>">Ban</a>
                            </div>
                        </td>
                        <td><?=$user->username?></td>
                        <td><?=date('m/d/Y',strtotime ($flag->date_created))?></td>
                    </tr>

                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <?=$pagination->paginate()?>

    <?php else: ?>
        <div class="block"><strong>No flags found</strong></div>
    <?php endif; ?>

</div>

<?php include ('footer.php'); ?>