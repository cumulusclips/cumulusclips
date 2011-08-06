<?php

### Created on February 28, 2009
### Created by Miguel A. Hurtado
### This script displays the site homepage


// Include required files
include ('../cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Filesystem');


// Establish page variables, objects, arrays, etc
Plugin::Trigger ('admin.video_edit.start');
//$logged_in = User::LoginCheck(HOST . '/login/');
//$admin = new User ($logged_in);
$page_title = 'Add Plugin';
$message = null;






/***********************
Handle form if submitted
***********************/

if (isset ($_POST['submitted'])) {

    // Validate title
    if (!empty ($_POST['title']) && !ctype_space ($_POST['title'])) {
        $data['title'] = htmlspecialchars (trim ($_POST['title']));
    } else {
        $Errors['title'] = Language::GetText('error_title');
    }


    // Validate description
    if (!empty ($_POST['description']) && !ctype_space ($_POST['description'])) {
        $data['description'] = htmlspecialchars (trim ($_POST['description']));
    } else {
        $Errors['description'] = Language::GetText('error_description');
    }


    // Validate tags
    if (!empty ($_POST['tags']) && !ctype_space ($_POST['tags'])) {
        $data['tags'] = htmlspecialchars (trim ($_POST['tags']));
    } else {
        $Errors['tags'] = Language::GetText('error_tags');
    }


    // Validate cat_id
    if (!empty ($_POST['cat_id']) && is_numeric ($_POST['cat_id'])) {
        $data['cat_id'] = $_POST['cat_id'];
    } else {
        $Errors['cat_id'] = Language::GetText('error_category');
    }


    // Validate status
    if (!empty ($_POST['status']) && !ctype_space ($_POST['status'])) {
        $data['status'] = htmlspecialchars (trim ($_POST['status']));
    } else {
        $Errors['status'] = 'Invalid status';
    }


    // Update video if no errors were made
    if (empty ($Errors)) {

        // Perform addional actions based on status change
        if ($data['status'] != $video->status) {

            // Handle "Approve" action
            if ($data['status'] == 'approved') {
                $video->Approve (true);
            }

            // Handle "Ban" action
            else if ($data['status'] == 'banned') {
                Flag::FlagDecision ($video->video_id, 'video', true);
            }

        }

        $video->Update ($data);
        $message = Language::GetText('success_video_updated');
        $message_type = 'success';
        Plugin::Trigger ('admin.video_edit.update_video');
    } else {
        $message = Language::GetText('errors_below');
        $message .= '<br /><br /> - ' . implode ('<br /> - ', $Errors);
        $message_type = 'error';
    }

}


// Output Header
include ('header.php');

?>

<div id="plugins-add">

    <h1>Add Plugins</h1>

    <?php if ($message): ?>
    <div class="<?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <div class="block">

        <p class="row-shift">If you have a plugin in .zip format use this form
        to upload and add it to the system.</p>

        <form action="<?=ADMIN?>/plugins_add.php" method="post">

            <div class="row<?=(isset ($Errors['tags'])) ? ' errors' : ''?>">
                <label>*Zip File:</label>
                <input id="upload-visible" class="text" type="text" name="upload-visible" />
                <input id="browse-button" type="button" class="button" value="Browse" />
                <input id="upload" type="file" name="upload" value="" />
            </div>

            <div class="row<?=(isset ($Errors['tags'])) ? ' errors' : ''?>">
                <label>*Activation:</label>
                <div id="activation-options">
                    <input type="radio" name="activation" id="auto-activate" value="auto-activate" checked="checked" />
                    <label for="auto-activate">Automatically activate plugin</label>

                    <input type="radio" name="activation" id="dont-activate" value="dont-activate" />
                    <label for="dont-activate">Upload but do not activate plugin</label>
                </div>
            </div>

            <div class="row-shift">
                <input type="hidden" name="submitted" value="TRUE" />
                <input type="submit" class="button" value="Add Plugin" />
            </div>
        </form>

    </div>


</div>

<?php include ('footer.php'); ?>