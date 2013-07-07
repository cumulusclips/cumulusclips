<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');
App::LoadClass ('Flag');
App::LoadClass ('Comment');


// Establish page variables, objects, arrays, etc
Functions::RedirectIf ($logged_in = User::LoginCheck(), HOST . '/login/');
$admin = new User ($logged_in);
Functions::RedirectIf (User::CheckPermissions ('admin_panel', $admin), HOST . '/myaccount/');
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
if (isset ($_GET['id']) && is_numeric ($_GET['id']) && $_GET['id'] != 0) {

    ### Retrieve record information
    $comment = new Comment ($_GET['id']);
    if ($comment->found) {
        $video = new Video ($comment->video_id);
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
    if ($comment->user_id == 0) {

        // Validate Name
        if (!empty ($_POST['name']) && !ctype_space ($_POST['name'])) {
            $data['name'] = htmlspecialchars ($_POST['name']);
        } else {
            $errors['name'] = 'Invalid name';
        }


        // Validate Email
        if (!empty ($_POST['email']) && !ctype_space ($_POST['email']) && preg_match ('/^[a-z0-9][a-z0-9_\.\-]+@[a-z0-9][a-z0-9\.\-]+\.[a-z0-9]{2,4}$/i',$_POST['email'])) {
            $data['email'] = $_POST['email'];
        } else {
            $errors['email'] = 'Invalid email address';
        }


        // Validate website
        if (!empty ($comment->website) && empty ($_POST['website'])) {
            $data['website'] = '';
        } else if (!empty ($_POST['website']) && !ctype_space ($_POST['website'])) {
            $website = $_POST['website'];
            if (preg_match ('/^(https?:\/\/)?[a-z0-9][a-z0-9\.-]+\.[a-z0-9]{2,4}.*$/i', $website, $matches)) {
                $website = (empty($matches[1])) ? 'http://' . $website : $website;
                $data['website'] = htmlspecialchars (trim ($website));
            } else {
                $errors['website'] = 'Invalid website';
            }
        }

    }   // END VALIDATE ANONYMOUS POSTER FIELDS


    // Validate status
    if (!empty ($_POST['status']) && !ctype_space ($_POST['status'])) {
        $data['status'] = htmlspecialchars (trim ($_POST['status']));
    } else {
        $errors['status'] = 'Invalid status';
    }


    // Validate comments
    if (!empty ($_POST['comments']) && !ctype_space ($_POST['comments'])) {
        $data['comments'] = htmlspecialchars (trim ($_POST['comments']));
    } else {
        $errors['comments'] = 'Invalid comments';
    }



    // Update record if no errors were found
    if (empty ($errors)) {

        // Perform addional actions based on status change
        if ($data['status'] != $comment->status) {

            // Handle "Approve" action
            if ($data['status'] == 'approved') {
                $comment->Approve ('approve');
            }

            // Handle "Ban" action
            else if ($data['status'] == 'banned') {
                Flag::FlagDecision ($comment->comment_id, 'comment', true);
            }

        }

        $message = 'Comment has been updated';
        $message_type = 'success';
        $comment->Update ($data);

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
    <div class="<?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <div class="block">

        <p><a href="<?=$list_page?>">Return to previous screen</a></p>

        <form action="<?=ADMIN?>/comments_edit.php?id=<?=$comment->comment_id?>" method="post">

            <div class="row<?=(isset ($errors['status'])) ? ' error' : ''?>">
                <label>Status:</label>
                <select name="status" class="dropdown">
                    <option value="approved"<?=(isset($data['status']) && $data['status'] == 'approved') || (!isset ($data['status']) && $comment->status == 'approved')?' selected="selected"':''?>>Approved</option>
                    <option value="pending"<?=(isset($data['status']) && $data['status'] == 'pending') || (!isset ($data['status']) && $comment->status == 'pending')?' selected="selected"':''?>>Pending</option>
                    <option value="banned"<?=(isset($data['status']) && $data['status'] == 'banned') || (!isset ($data['status']) && $comment->status == 'banned')?' selected="selected"':''?>>Banned</option>
                </select>
            </div>

            <div class="row"><label>Date Posted:</label>
                <?=Functions::DateFormat('m/d/Y',$comment->date_created)?>
            </div>

            <div class="row">
                <label>In Response To:</label>
                <a target="_ccsite" href="<?=$video->url?>/"><?=$video->title?></a>
            </div>

            <?php if ($comment->user_id == 0): ?>

                <div class="row<?=(isset ($errors['name'])) ? ' error' : ''?>">
                    <label>*Name:</label>
                    <input class="text" type="text" name="name" value="<?=(isset ($data['name'])) ? $data['name'] : $comment->name?>" />
                </div>

                <div class="row<?=(isset ($errors['email'])) ? ' error' : ''?>">
                    <label>*Email:</label>
                    <input class="text" type="text" name="email" value="<?=(isset ($data['email'])) ? $data['email'] : $comment->email?>" />
                </div>

                <div class="row<?=(isset ($errors['website'])) ? ' error' : ''?>">
                    <label>Website:</label>
                    <input class="text" type="text" name="website" value="<?=(isset ($data['website'])) ? $data['website'] : $comment->website?>" />
                </div>

            <?php else: ?>

                <div class="row">
                    <label>Username:</label>
                    <a target="_ccsite" href="<?=$comment->website?>"><?=$comment->name?></a>
                </div>

            <?php endif; ?>

            <div class="row<?=(isset ($errors['comment'])) ? ' error' : ''?>">
                <label>Comments:</label>
                <textarea rows="7" cols="50" class="text" name="comments"><?=(isset ($data['comments'])) ? $data['comments'] : $comment->comments?></textarea>
            </div>

            <div class="row-shift">
                <input type="hidden" value="yes" name="submitted" />
                <input type="submit" class="button" value="Update Comment" />
            </div>

        </form>

    </div>

</div>

<?php include ('footer.php'); ?>