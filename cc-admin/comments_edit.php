<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$userService = new UserService();
$adminUser = $userService->loginCheck();
Functions::RedirectIf($adminUser, HOST . '/login/');
Functions::RedirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$commentMapper = new CommentMapper();
$commentService = new CommentService();
$videoMapper = new VideoMapper();
$videoService = new VideoService();
$userMapper = new UserMapper();
$page_title = 'Edit Comment';
$data = array();
$errors = array();
$message = null;



// Build return to list link
if (!empty ($_SESSION['list_page'])) {
    $list_page = $_SESSION['list_page'];
} else {
    $list_page = ADMIN . '/comments.php';
}



### Verify a record was provided
if (isset ($_GET['id']) && is_numeric ($_GET['id']) && $_GET['id'] > 0) {

    ### Retrieve record information
    $comment = $commentMapper->getCommentById($_GET['id']);
    if ($comment) {
        $video = $videoMapper->getVideoById($comment->videoId);
    } else {
        header ('Location: ' . ADMIN . '/comments.php');
        exit();
    }

} else {
    header ('Location: ' . ADMIN . '/comments.php');
    exit();
}





/***********************
Handle form if submitted
***********************/

if (isset ($_POST['submitted'])) {


    // Validate user fields if anonymous
    if ($comment->userId == 0) {

        // Validate Name
        if (!empty ($_POST['name']) && !ctype_space ($_POST['name'])) {
            $comment->name = trim($_POST['name']);
        } else {
            $errors['name'] = 'Invalid name';
        }


        // Validate Email
        if (!empty ($_POST['email']) && !ctype_space ($_POST['email']) && preg_match ('/^[a-z0-9][a-z0-9_\.\-]+@[a-z0-9][a-z0-9\.\-]+\.[a-z0-9]{2,4}$/i',$_POST['email'])) {
            $comment->email = trim($_POST['email']);
        } else {
            $errors['email'] = 'Invalid email address';
        }


        // Validate website
        if (!empty ($comment->website) && empty ($_POST['website'])) {
            $comment->website = null;
        } else if (!empty ($_POST['website']) && !ctype_space ($_POST['website'])) {
            $website = $_POST['website'];
            if (preg_match ('/^(https?:\/\/)?[a-z0-9][a-z0-9\.-]+\.[a-z0-9]{2,4}.*$/i', $website, $matches)) {
                $website = (empty($matches[1])) ? 'http://' . $website : $website;
                $comment->website = trim ($website);
            } else {
                $errors['website'] = 'Invalid website';
            }
        }

    }   // END VALIDATE ANONYMOUS POSTER FIELDS


    // Validate status
    if (!empty ($_POST['status']) && !ctype_space ($_POST['status'])) {
        $newCommentStatus = trim ($_POST['status']);
    } else {
        $errors['status'] = 'Invalid status';
    }


    // Validate comments
    if (!empty ($_POST['comments']) && !ctype_space ($_POST['comments'])) {
        $comment->comments = trim ($_POST['comments']);
    } else {
        $errors['comments'] = 'Invalid comments';
    }



    // Update record if no errors were found
    if (empty ($errors)) {

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
        $message_type = 'success';
        $commentMapper->save($comment);

    } else {
        $message = 'The following errors were found. Please correct them and try again.';
        $message .= '<br /><br /> - ' . implode ('<br /> - ', $errors);
        $message_type = 'errors';
    }

}


// Output Header
include ('header.php');

?>

<div id="comments-edit">

    <h1>Update Comment</h1>

    <?php if ($message): ?>
    <div class="message <?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <div class="block">

        <p><a href="<?=$list_page?>">Return to previous screen</a></p>

        <form action="<?=ADMIN?>/comments_edit.php?id=<?=$comment->commentId?>" method="post">

            <div class="row<?=(isset ($errors['status'])) ? ' error' : ''?>">
                <label>Status:</label>
                <select name="status" class="dropdown">
                    <option value="approved"<?=(isset($data['status']) && $data['status'] == 'approved') || (!isset ($data['status']) && $comment->status == 'approved')?' selected="selected"':''?>>Approved</option>
                    <option value="pending"<?=(isset($data['status']) && $data['status'] == 'pending') || (!isset ($data['status']) && $comment->status == 'pending')?' selected="selected"':''?>>Pending</option>
                    <option value="banned"<?=(isset($data['status']) && $data['status'] == 'banned') || (!isset ($data['status']) && $comment->status == 'banned')?' selected="selected"':''?>>Banned</option>
                </select>
            </div>

            <div class="row"><label>Date Posted:</label>
                <?=Functions::DateFormat('m/d/Y',$comment->dateCreated)?>
            </div>

            <div class="row">
                <label>In Response To:</label>
                <a target="_ccsite" href="<?=$videoService->getUrl($video)?>/"><?=$video->title?></a>
            </div>

            <?php if ($comment->userId == 0): ?>

                <div class="row<?=(isset ($errors['name'])) ? ' error' : ''?>">
                    <label>*Name:</label>
                    <input class="text" type="text" name="name" value="<?=htmlspecialchars($comment->name)?>" />
                </div>

                <div class="row<?=(isset ($errors['email'])) ? ' error' : ''?>">
                    <label>*Email:</label>
                    <input class="text" type="text" name="email" value="<?=htmlspecialchars($comment->email)?>" />
                </div>

                <div class="row<?=(isset ($errors['website'])) ? ' error' : ''?>">
                    <label>Website:</label>
                    <input class="text" type="text" name="website" value="<?=htmlspecialchars($comment->website)?>" />
                </div>

            <?php else: ?>

                <?php $user = $userMapper->getUserById($comment->userId); ?>
                <div class="row">
                    <label>Username:</label>
                    <a target="_ccsite" href="<?=HOST . '/members/' . $user->username?>/"><?=$user->username?></a>
                </div>

            <?php endif; ?>

            <div class="row<?=(isset ($errors['comment'])) ? ' error' : ''?>">
                <label>Comments:</label>
                <textarea rows="7" cols="50" class="text" name="comments"><?=htmlspecialchars($comment->comments)?></textarea>
            </div>

            <div class="row-shift">
                <input type="hidden" value="yes" name="submitted" />
                <input type="submit" class="button" value="Update Comment" />
            </div>

        </form>

    </div>

</div>

<?php include ('footer.php'); ?>