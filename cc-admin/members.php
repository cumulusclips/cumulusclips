<?php

### Created on February 28, 2009
### Created by Miguel A. Hurtado
### This script displays the site homepage


// Include required files
include ('../cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Flag');
App::LoadClass ('Pagination');


// Establish page variables, objects, arrays, etc
Plugin::Trigger ('admin.members.start');
//$logged_in = User::LoginCheck(HOST . '/login/');
//$admin = new User ($logged_in);
$records_per_page = 9;
$url = ADMIN . '/members.php';
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
else if (!empty ($_GET['ban']) && is_numeric ($_GET['ban'])) {

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
$status = (!empty ($_GET['status'])) ? $_GET['status'] : 'active';
switch ($status) {

    case 'new':
        $query_string['status'] = 'new';
        $header = 'New Members';
        $page_title = 'New Members';
        break;
    case 'pending':
        $query_string['status'] = 'pending';
        $header = 'Pending Members';
        $page_title = 'Pending Members';
        break;
    case 'banned':
        $query_string['status'] = 'banned';
        $header = 'Banned Members';
        $page_title = 'Banned Members';
        break;
    default:
        $status = 'active';
        $header = 'Active Members';
        $page_title = 'Active Members';
        break;

}
$query = "SELECT user_id FROM " . DB_PREFIX . "users WHERE status = '$status'";



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
$query .= " ORDER BY user_id DESC";
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

<div id="members">

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
            <select name="status" data-jump="<?=ADMIN?>/members.php">
                <option <?=(isset($status) && $status == 'active') ? 'selected="selected"' : ''?>value="active">Active</option>
                <option <?=(isset($status) && $status == 'new') ? 'selected="selected"' : ''?>value="new">New</option>
                <option <?=(isset($status) && $status == 'pending') ? 'selected="selected"' : ''?>value="pending">Pending</option>
                <option <?=(isset($status) && $status == 'banned') ? 'selected="selected"' : ''?>value="banned">Banned</option>
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
                        <td class="large">Member</td>
                        <td class="large">Email</td>
                        <td class="large">Date Joined</td>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $db->FetchObj ($result)): ?>

                    <?php $odd = empty ($odd) ? true : false; ?>
                    <?php $user = new User ($row->user_id); ?>

                    <tr class="<?=$odd ? 'odd' : ''?>">
                        <td>
                            <a href="<?=ADMIN?>/members_edit.php?id=<?=$user->user_id?>" class="large"><?=$user->username?></a>
                            <div class="record-actions invisible">
                                <a href="<?=HOST?>/members/<?=$user->username?>/">View Profile</a>
                                <a href="<?=ADMIN?>/members_edit.php?id=<?=$user->user_id?>">Edit</a>

                                <?php if ($status == 'active'): ?>
                                    <a class="delete" href="<?=$pagination->GetURL('ban='.$user->user_id)?>">Ban</a>
                                <?php elseif (in_array ($status, array ('new', 'pending'))): ?>
                                    <a class="approve" href="<?=$pagination->GetURL('activate='.$user->user_id)?>">Activate</a>
                                <?php elseif ($status == 'banned'): ?>
                                    <a href="<?=$pagination->GetURL('unban='.$user->user_id)?>">Unban</a>
                                <?php endif; ?>

                                <a class="delete confirm" href="<?=$pagination->GetURL('delete='.$user->user_id)?>" data-confirm="You are about to delete this member and their content, this cannot be undone. Are you sure you want to do this?">Delete</a>
                            </div>
                        </td>
                        <td><?=$user->email?></td>
                        <td><?=$user->date_created?></td>
                    </tr>

                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <?=$pagination->paginate()?>

    <?php else: ?>
        <div class="block"><strong>No members found</strong></div>
    <?php endif; ?>

</div>

<?php include ('footer.php'); ?>