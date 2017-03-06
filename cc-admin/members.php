<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$authService->enforceTimeout(true);

// Verify user can access admin panel
$userService = new \UserService();
Functions::RedirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$userMapper = new UserMapper();
$records_per_page = 9;
$url = ADMIN . '/members.php';
$query_string = array();
$message = null;
$sub_header = null;

// Display permission denied message if requested
if (isset($_GET['denied'])) {
    $message = 'Permission Denied';
    $message_type = 'danger';
}


### Handle "Delete" member
if (!empty ($_GET['delete']) && is_numeric ($_GET['delete'])) {

    // Validate id
    $user = $userMapper->getUserById($_GET['delete']);
    if ($user->role == 'admin' && $adminUser->role != 'admin') {
        $message = 'Permission Denied';
        $message_type = 'danger';
    } else if ($user && $user->userId != $adminUser->userId) {
        $userService->delete($user);
        $message = 'Member has been deleted';
        $message_type = 'success';
    } else if ($user->userId == $adminUser->userId) {
        $message = 'You can\'t delete yourself, silly!';
        $message_type = 'danger';
    }
}


### Handle "Activate" member
else if (!empty ($_GET['activate']) && is_numeric ($_GET['activate'])) {

    // Validate id
    $user = $userMapper->getUserById($_GET['activate']);
    if ($user) {
        $userService->updateContentStatus($user, 'active');
        $userService->approve($user, 'approve');
        $message = 'Member has been activated';
        $message_type = 'success';
    }
}


### Handle "Unban" member
else if (!empty ($_GET['unban']) && is_numeric ($_GET['unban'])) {

    // Validate id
    $user = $userMapper->getUserById($_GET['unban']);
    if ($user) {
        $userService->updateContentStatus($user, 'active');
        $userService->approve($user, 'approve');
        $message = 'Member has been unbanned';
        $message_type = 'success';
    }
}


### Handle "Ban" member
else if (!empty ($_GET['ban']) && is_numeric ($_GET['ban'])) {

    // Validate id
    $user = $userMapper->getUserById($_GET['ban']);
    if ($user && $user->role == 'admin' && $adminUser->role != 'admin') {
        $message = 'Permission Denied';
        $message_type = 'danger';
    } else if ($user) {
        $user->status = 'banned';
        $userMapper->save($user);
        $userService->updateContentStatus($user, 'banned');
        $flagService = new FlagService();
        $flagService->flagDecision($user, true);
        $message = 'Member has been banned';
        $message_type = 'success';
    }
}




### Determine which type (account status) of members to display
$status = (!empty ($_GET['status'])) ? $_GET['status'] : 'active';
$pageName = 'members';
switch ($status) {

    case 'new':
        $query_string['status'] = 'new';
        $header = 'New Members';
        $page_title = 'New Members';
        $statusText = 'New';
        break;
    case 'pending':
        $query_string['status'] = 'pending';
        $header = 'Pending Members';
        $page_title = 'Pending Members';
        $statusText = 'Pending';
        $pageName = 'members-pending';
        break;
    case 'banned':
        $query_string['status'] = 'banned';
        $header = 'Banned Members';
        $page_title = 'Banned Members';
        $statusText = 'Banned';
        break;
    default:
        $status = 'active';
        $header = 'Active Members';
        $page_title = 'Active Members';
        $statusText = 'Active';
        break;

}
$query = 'SELECT user_id FROM ' . DB_PREFIX . 'users WHERE status = :status';
$queryParams = array(':status' => $status);



// Handle Search Member Form
if (isset ($_POST['search_submitted'])&& !empty ($_POST['search'])) {

    $like = trim($_POST['search']);
    $query_string['search'] = $like;
    $query .= ' AND username LIKE :like';
    $queryParams[':like'] = "%$like%";
    $sub_header = "Search Results for: <em>$like</em>";

} else if (!empty ($_GET['search'])) {

    $like = trim($_GET['search']);
    $query_string['search'] = $like;
    $query .= ' AND username LIKE :like';
    $queryParams[':like'] = "%$like%";
    $sub_header = "Search Results for: <em>$like</em>";

}



// Retrieve total count
$query .= " ORDER BY user_id DESC";
$db->fetchAll($query, $queryParams);
$total = $db->rowCount();

// Initialize pagination
$url .= (!empty ($query_string)) ? '?' . http_build_query($query_string) : '';
$pagination = new Pagination ($url, $total, $records_per_page, false);
$start_record = $pagination->GetStartRecord();
$_SESSION['list_page'] = $pagination->GetURL();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$userResults = $db->fetchAll($query, $queryParams);
$users = $userMapper->getUsersFromList(Functions::arrayColumn($userResults, 'user_id'));


// Output Header
include ('header.php');

?>

<h1><?=$header?></h1>
<?php if ($sub_header): ?>
<h3><?=$sub_header?></h3>
<?php endif; ?>


<?php if ($message): ?>
<div class="alert alert-<?=$message_type?>"><?=$message?></div>
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
            <li><a tabindex="-1" href="<?=ADMIN?>/members.php?status=active">Active</a></li>
            <li><a tabindex="-1" href="<?=ADMIN?>/members.php?status=new">New</a></li>
            <li><a tabindex="-1" href="<?=ADMIN?>/members.php?status=pending">Pending</a></li>
            <li><a tabindex="-1" href="<?=ADMIN?>/members.php?status=banned">Banned</a></li>
          </ul>
        </div>
    </div>

    <div class="search">
        <form method="POST" action="<?=ADMIN?>/members.php?status=<?=$status?>">
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
                    <th>Member</th>
                    <th>Email</th>
                    <th>Date Joined</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>

                <tr>
                    <td>
                        <a href="<?=ADMIN?>/members_edit.php?id=<?=$user->userId?>" class="h3"><?=$user->username?></a>
                        <div class="record-actions invisible">

                            <?php if ($status == 'active'): ?>
                                <a href="<?=HOST?>/members/<?=$user->username?>/">View Profile</a>
                            <?php endif; ?>

                            <a href="<?=ADMIN?>/members_edit.php?id=<?=$user->userId?>">Edit</a>

                            <?php if ($status == 'active'): ?>
                                <a class="delete" href="<?=$pagination->GetURL('ban='.$user->userId)?>">Ban</a>
                            <?php endif; ?>

                            <?php if (in_array ($status, array ('new', 'pending'))): ?>
                                <a class="approve" href="<?=$pagination->GetURL('activate='.$user->userId)?>">Activate</a>
                            <?php endif; ?>

                            <?php if ($status == 'banned'): ?>
                                <a href="<?=$pagination->GetURL('unban='.$user->userId)?>">Unban</a>
                            <?php endif; ?>

                            <a class="delete confirm" href="<?=$pagination->GetURL('delete='.$user->userId)?>" data-confirm="You're about to delete this member and their content. This cannot be undone. Do you want to proceed?">Delete</a>
                        </div>
                    </td>
                    <td><?=$user->email?></td>
                    <td><?=date('m/d/Y', strtotime($user->dateCreated))?></td>
                </tr>

            <?php endforeach; ?>
            </tbody>
        </table>

<?php else: ?>
    <p>No members found</p>
<?php endif; ?>


<?php include ('footer.php'); ?>