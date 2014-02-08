<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/config/admin.bootstrap.php');

// Verify if user is logged in
$userService = new UserService();
$adminUser = $userService->loginCheck();
Functions::RedirectIf($adminUser, HOST . '/login/');
Functions::RedirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/myaccount/');

// Establish page variables, objects, arrays, etc
$commentMapper = new CommentMapper();
$videoMapper = new VideoMapper();
$videoService = new VideoService();
$userMapper = new UserMapper();
$flagService = new FlagService();
$flagMapper = new FlagMapper();
$records_per_page = 9;
$url = ADMIN . '/flags.php';
$query_string = array();
$message = null;



// Verify content type was provided
if (!empty ($_GET['status']) && in_array ($_GET['status'], array ('video', 'user', 'comment'))) {
    $type = $_GET['status'];
} else {
    $type = 'video';
}




### Handle "Ban" record
if (!empty ($_GET['ban']) && is_numeric ($_GET['ban'])) {

    switch ($type) {

        case 'video':
            $video = $videoMapper->getVideoById($_GET['ban']);
            if ($video) {
                $video->status = 'banned';
                $videoMapper->save($video);
                $flagService->flagDecision($video, true);
                $message = 'Video has been banned';
                $message_type = 'success';
            }
            break;

        case 'user':
            $user = $userMapper->getUserById($_GET['ban']);
            if ($user) {
                $user->status = 'banned';
                $userMapper->save($user);
                $flagService->flagDecision($user, true);
                $userService->updateContentStatus ($user, 'banned');
                $message = 'Member has been banned';
                $message_type = 'success';
            }
            break;

        case 'comment':
            $comment = $commentMapper->getCommentById($_GET['ban']);
            if ($comment) {
                $comment->status = 'banned';
                $commentMapper->save($comment);
                $flagService->flagDecision($comment, true);
                $message = 'Comment has been banned';
                $message_type = 'success';
            }
            break;

    }

}



### Handle "Dismiss" flags
else if (!empty ($_GET['dismiss']) && is_numeric ($_GET['dismiss'])) {
    
    switch ($type) {
        case 'video':
            $contentObject = $videoMapper->getVideoById($_GET['dismiss']);
            break;

        case 'user':
            $contentObject = $userMapper->getUserById($_GET['dismiss']);
            break;

        case 'comment':
            $contentObject = $commentMapper->getCommentById($_GET['dismiss']);
            break;
    }
    
    if ($contentObject) {
        $flagService->flagDecision($contentObject, false);
        $message = 'Flags has been dismissed';
        $message_type = 'success';
    }
}




### Determine which type (account status) of members to display
switch ($type) {

    case 'user':
        $query_string['status'] = 'user';
        $header = 'Flagged Member';
        $page_title = 'Flagged Members';
        break;
    case 'video':
        $query_string['status'] = 'video';
        $header = 'Flagged Videos';
        $page_title = 'Flagged Videos';
        break;
    case 'comment':
        $query_string['status'] = 'comment';
        $header = 'Flagged Comments';
        $page_title = 'Flagged Comments';
        break;

}
$query = "SELECT flag_id FROM " . DB_PREFIX . "flags WHERE status = 'pending' AND type = '$type'";

// Retrieve total count
$db->fetchRow($query);
$total = $db->rowCount();

// Initialize pagination
$url .= (!empty ($query_string)) ? '?' . http_build_query($query_string) : '';
$pagination = new Pagination ($url, $total, $records_per_page, false);
$start_record = $pagination->GetStartRecord();
$_SESSION['list_page'] = $pagination->GetURL();

// Retrieve limited results
$query .= " LIMIT $start_record, $records_per_page";
$flagResult = $db->fetchAll($query);
$flagsList = $flagMapper->getFlagsFromList(
    Functions::arrayColumn($flagResult, 'flag_id')
);


// Output Header
include ('header.php');

?>

<div id="flags">

    <h1><?=$header?></h1>

    <?php if ($message): ?>
    <div class="message <?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <div id="browse-header">
        <div class="jump">
            Jump To:
            <select name="status" data-jump="<?=ADMIN?>/flags.php">
                <option <?=($type == 'video') ? 'selected="selected"' : ''?>value="video">Videos</option>
                <option <?=($type == 'user') ? 'selected="selected"' : ''?>value="user">Members</option>
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
                <?php foreach ($flagsList as $flag): ?>

                    <?php $odd = empty ($odd) ? true : false; ?>
                    <?php $reporter = $userMapper->getUserById($flag->userId); ?>

                    <tr class="<?=$odd ? 'odd' : ''?>">
                        <td>

                            <?php if ($type == 'user'): ?>

                                <?php $user = $userMapper->getUserById($flag->objectId); ?>
                                <a href="<?=ADMIN?>/members_edit.php?id=<?=$user->userId?>" class="large"><?=$user->username?></a>
                                <div class="record-actions invisible">
                                    <a href="<?=HOST?>/members/<?=$user->username?>/">View Profile</a>
                                    <a href="<?=ADMIN?>/members_edit.php?id=<?=$user->userId?>">Edit</a>

                            <?php elseif ($type == 'video'): ?>

                                <?php $video = $videoMapper->getVideoById($flag->objectId); ?>
                                <a href="<?=ADMIN?>/videos_edit.php?id=<?=$video->videoId?>" class="large"><?=$video->title?></a>
                                <div class="record-actions invisible">
                                    <a href="<?=$videoService->getUrl($video)?>/">Watch Video</a>
                                    <a href="<?=ADMIN?>/videos_edit.php?id=<?=$video->videoId?>">Edit</a>

                            <?php elseif ($type == 'comment'): ?>

                                <?php $comment = $commentMapper->getCommentById($flag->objectId); ?>
                                <?php $video = $videoMapper->getVideoById($comment->videoId); ?>

                                <?=$comment->comments_display?>
                                <div class="record-actions invisible">
                                    <a href="<?=ADMIN?>/comments_edit.php?id=<?=$comment->commentId?>">Edit</a>

                            <?php endif; ?>

                                <a href="<?=$pagination->GetURL('dismiss='.$flag->objectId)?>">Dismiss Flag</a>
                                <a class="delete" href="<?=$pagination->GetURL('ban='.$flag->objectId)?>">Ban</a>
                            </div>
                        </td>
                        <td><?=$reporter->username?></td>
                        <td><?=Functions::DateFormat('m/d/Y',$flag->dateCreated)?></td>
                    </tr>

                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?=$pagination->paginate()?>

    <?php else: ?>
        <div class="block"><strong>No flags found</strong></div>
    <?php endif; ?>

</div>

<?php include ('footer.php'); ?>