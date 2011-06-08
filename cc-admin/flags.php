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



### Handle "Delete" member
if (!empty ($_GET['delete']) && is_numeric ($_GET['delete'])) {

    // Validate id
    if (User::Exist (array ('user_id' => $_GET['delete']))) {
        User::Delete ($_GET['delete']);
        $message = 'Member has been deleted';
        $message_type = 'success';
    }

}


### Handle "Activate" member
else if (!empty ($_GET['activate']) && is_numeric ($_GET['activate'])) {

    // Validate id
    $user = new User ($_GET['activate']);
    if ($user->found) {
        $user->UpdateContentStatus ('active');
        $user->Approve (true);
        $message = 'Member has been activated';
        $message_type = 'success';
    }

}


### Handle "Unban" member
else if (!empty ($_GET['unban']) && is_numeric ($_GET['unban'])) {

    // Validate id
    $user = new User ($_GET['unban']);
    if ($user->found) {
        $user->UpdateContentStatus ('active');
        $user->Approve (true);
        $message = 'Member has been unbanned';
        $message_type = 'success';
    }

}


### Handle "Ban" member
else if (!empty ($_GET['dismiss']) && is_numeric ($_GET['dismiss'])) {

    // Validate id
    $user = new User ($_GET['ban']);
    if ($user->found) {
        $user->Update (array ('status' => 'banned'));
        $user->UpdateContentStatus ('banned');
        Flag::FlagDecision($user->user_id, 'user', true);
        $message = 'Member has been banned';
        $message_type = 'success';
    }

}




### Determine which type (account status) of members to display
$type = (!empty ($_GET['type'])) ? $_GET['type'] : '';
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
    default:
        App::Throw404();
        break;

}
$query = "SELECT flag_id FROM " . DB_PREFIX . "flags WHERE type = '$type'";



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
    <?php if ($sub_header): ?>
    <h3><?=$sub_header?></h3>
    <?php endif; ?>


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

        <div class="search">
            <form method="POST" action="<?=ADMIN?>/members.php?status=<?=$status?>">
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
                        <td class="large">Content</td>
                        <td class="large">Flagged By</td>
                        <td class="large">Date Flagged</td>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $db->FetchObj ($result)): ?>

                    <?php $odd = empty ($odd) ? true : false; ?>
                    <?php $flag = new Flag ($row->flag_id); ?>

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
                                <?=$comment->comments_display?>
                                <div class="record-actions invisible">
                                    <a href="<?=ADMIN?>/comment_edit.php?id=<?=$comment->comment_id?>">Edit</a>

                            <?php endif; ?>

                                <a href="<?=$pagination->GetURL('dismiss='.$flag->id)?>">Dismiss Flag</a>
                                <a class="delete" href="<?=$pagination->GetURL('approve='.$flag->id)?>">Ban</a>
                            </div>
                        </td>
                        <td><?=$flag->user_id?></td>
                        <td><?=$flag->date_created?></td>
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