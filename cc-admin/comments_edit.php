<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$authService->enforceTimeout(true);

// Verify user can access admin panel
$userService = new \UserService();
Functions::RedirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$commentMapper = new CommentMapper();
$commentService = new CommentService();
$videoMapper = new VideoMapper();
$videoService = new VideoService();
$userMapper = new UserMapper();
$page_title = 'Edit Comment';
$pageName = 'comments-edit';
$data = array();
$errors = array();
$message = null;

// Build return to list link
if (!empty($_SESSION['list_page'])) {
    $list_page = $_SESSION['list_page'];
} else {
    $list_page = ADMIN . '/comments.php';
}

// Verify a record was provided
if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0) {

    // Retrieve record information
    $comment = $commentMapper->getCommentById($_GET['id']);
    if ($comment) {
        $video = $videoMapper->getVideoById($comment->videoId);
    } else {
        header('Location: ' . ADMIN . '/comments.php');
        exit();
    }
} else {
    header('Location: ' . ADMIN . '/comments.php');
    exit();
}

// Handle form if submitted
if (isset($_POST['submitted'])) {

    // Validate form nonce token and submission speed
    if (
        !empty($_POST['nonce'])
        && !empty($_SESSION['formNonce'])
        && !empty($_SESSION['formTime'])
        && $_POST['nonce'] == $_SESSION['formNonce']
        && time() - $_SESSION['formTime'] >= 2
    ) {
        // Validate status
        if (!empty($_POST['status']) && !ctype_space($_POST['status'])) {
            $newCommentStatus = trim ($_POST['status']);
        } else {
            $errors['status'] = 'Invalid status';
        }

        // Validate comments
        if (!empty($_POST['comments']) && !ctype_space($_POST['comments'])) {
            $comment->comments = trim ($_POST['comments']);
        } else {
            $errors['comments'] = 'Invalid comments';
        }

        // Update record if no errors were found
        if (empty($errors)) {

            // Perform addional actions based on status change
            if ($newCommentStatus != $comment->status) {

                $comment->status = $newCommentStatus;

                // Handle "Approve" action
                if ($comment->status == 'approved') {
                    $commentService->approve($comment, 'approve');
                }

                // Handle "Ban" action
                else if ($comment->status == 'banned') {
                    $flagService = new FlagService();
                    $flagService->flagDecision($comment, true);
                }
            }

            $message = 'Comment has been updated';
            $message_type = 'alert-success';
            $commentMapper->save($comment);
        } else {
            $message = 'The following errors were found. Please correct them and try again.';
            $message .= '<br /><br /> - ' . implode('<br /> - ', $errors);
            $message_type = 'alert-danger';
        }

    } else {
        $message = 'Expired or invalid session';
        $message_type = 'alert-danger';
    }
}

// Generate new form nonce
$formNonce = md5(uniqid(rand(), true));
$_SESSION['formNonce'] = $formNonce;
$_SESSION['formTime'] = time();

// Output Header
include('header.php');

?>

<h1>Update Comment</h1>

<?php if ($message): ?>
<div class="alert <?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<p><a href="<?=$list_page?>">Return to previous screen</a></p>

<form action="<?=ADMIN?>/comments_edit.php?id=<?=$comment->commentId?>" method="post">

    <?php $user = $userMapper->getUserById($comment->userId); ?>
    <div class="form-group">
        <p><strong>Date Posted:</strong> <?=date('m/d/Y', strtotime($comment->dateCreated))?></p>
        <p><strong>Video:</strong> <a target="_ccsite" href="<?=$videoService->getUrl($video)?>/"><?=$video->title?></a></p>
        <p><strong>Username:</strong> <a target="_ccsite" href="<?=HOST . '/members/' . $user->username?>/"><?=$user->username?></a></p>
    </div>

    <div class="form-group <?=(isset($errors['status'])) ? 'has-error' : ''?>">
        <label class="control-label">Status:</label>
        <select name="status" class="form-control">
            <option value="approved"<?=(isset($data['status']) && $data['status'] == 'approved') || (!isset($data['status']) && $comment->status == 'approved')?' selected="selected"':''?>>Approved</option>
            <option value="pending"<?=(isset($data['status']) && $data['status'] == 'pending') || (!isset($data['status']) && $comment->status == 'pending')?' selected="selected"':''?>>Pending</option>
            <option value="banned"<?=(isset($data['status']) && $data['status'] == 'banned') || (!isset($data['status']) && $comment->status == 'banned')?' selected="selected"':''?>>Banned</option>
        </select>
    </div>

    <div class="form-group <?=(isset($errors['comments'])) ? 'has-error' : ''?>">
        <label class="control-label">Comments:</label>
        <textarea rows="7" cols="50" class="form-control" name="comments"><?=htmlspecialchars($comment->comments)?></textarea>
    </div>

    <input type="hidden" value="yes" name="submitted" />
    <input type="hidden" name="nonce" value="<?=$formNonce?>" />
    <input type="submit" class="button" value="Update Comment" />

</form>

<?php include('footer.php'); ?>