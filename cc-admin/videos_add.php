<?php

### Created on February 28, 2009
### Created by Miguel A. Hurtado
### This script displays the site homepage


// Include required files
include ('../cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');
App::LoadClass ('Flag');


// Establish page variables, objects, arrays, etc
Plugin::Trigger ('admin.video_edit.start');
//$logged_in = User::LoginCheck(HOST . '/login/');
//$admin = new User ($logged_in);
$page_title = 'Add Video';
$categories = array();
$data = array();
$errors = array();
$message = null;
$admin_js[] = ADMIN . '/extras/uploadify/swfobject.js';
$admin_js[] = ADMIN . '/extras/uploadify/jquery.uploadify.v2.1.4.min.js';
$admin_js[] = ADMIN . '/js/uploadify.js';



// Retrieve Category names
$query = "SELECT cat_id, cat_name FROM " . DB_PREFIX . "categories";
$result = $db->Query ($query);
while ($row = $db->FetchObj ($result)) {
    $categories[$row->cat_id] = $row->cat_name;
}





/***********************
Handle form if submitted
***********************/

if (isset ($_POST['submitted'])) {

    // Validate title
    if (!empty ($_POST['title']) && !ctype_space ($_POST['title'])) {
        $data['title'] = htmlspecialchars (trim ($_POST['title']));
    } else {
        $errors['title'] = 'Invalid title';
    }


    // Validate description
    if (!empty ($_POST['description']) && !ctype_space ($_POST['description'])) {
        $data['description'] = htmlspecialchars (trim ($_POST['description']));
    } else {
        $errors['description'] = 'Invalid description';
    }


    // Validate tags
    if (!empty ($_POST['tags']) && !ctype_space ($_POST['tags'])) {
        $data['tags'] = htmlspecialchars (trim ($_POST['tags']));
    } else {
        $errors['tags'] = 'Invalid tags';
    }


    // Validate cat_id
    if (!empty ($_POST['cat_id']) && is_numeric ($_POST['cat_id'])) {
        $data['cat_id'] = $_POST['cat_id'];
    } else {
        $errors['cat_id'] = 'Invalid category';
    }


    // Validate status
    if (!empty ($_POST['status']) && !ctype_space ($_POST['status'])) {
        $data['status'] = htmlspecialchars (trim ($_POST['status']));
    } else {
        $errors['status'] = 'Invalid status';
    }


    // Update video if no errors were made
    if (empty ($errors)) {

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
        $message = 'Video has been updated.';
        $message_type = 'success';
        Plugin::Trigger ('admin.video_edit.update_video');
    } else {
        $message = 'The following errors were found. Please correct them and try again.';
        $message .= '<br /><br /> - ' . implode ('<br /> - ', $errors);
        $message_type = 'error';
    }

}


// Output Header
include ('header.php');

?>

<link type="text/css" rel="stylesheet" href="<?=ADMIN?>/extras/uploadify/uploadify.css" />
<div id="videos-add">

    <h1>Add Video</h1>

    <?php if ($message): ?>
    <div class="<?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <div class="block">

        <form action="<?=ADMIN?>/videos_add.php" method="post">

            <div class="row <?=(isset ($errors['title'])) ? 'errors' : ''?>">
                <label>Video File:</label>
                <div id="upload-box">
                    <input id="browse-button" type="button" class="button" value="Browse" />
                    <input id="upload" type="file" name="upload" />
                    <input id="upload-button" type="button" class="button" value="Upload" />
                </div>
            </div>

            <div class="row <?=(isset ($errors['title'])) ? 'errors' : ''?>">
                <label>Title:</label>
                <input class="text" type="text" name="title" value="<?=(isset ($data['title'])) ? $data['title'] : ''?>" />
            </div>

            <div class="row <?=(isset ($errors['description'])) ? 'errors' : ''?>">
                <label>Description:</label>
                <textarea rows="7" cols="50" class="text" name="description"><?=(isset ($data['title'])) ? $data['title'] : ''?></textarea>
            </div>

            <div class="row <?=(isset ($errors['tags'])) ? 'errors' : ''?>">
                <label>Tags:</label>
                <input class="text" type="text" name="tags" value="<?=(isset ($data['title'])) ? $data['title'] : ''?>" /> (Comma Delimited)
            </div>

            <div class="row <?=(isset ($errors['cat_id'])) ? 'errors' : ''?>">
                <label>Category:</label>
                <select class="dropdown" name="cat_id">
                <?php foreach ($categories as $cat_id => $cat_name): ?>
                    <option value="<?=$cat_id?>" <?=(isset ($data['title']) && $data['title'] == $cat_id) ? '' : 'selected="selected"'?>><?=$cat_name?></option>
                <?php endforeach; ?>
                </select>
            </div>

            <div class="row-shift">
                <input type="hidden" name="video" value="" />
                <input type="hidden" name="submitted" value="TRUE" />
                <input type="submit" class="button" value="Add Video" />
            </div>
        </form>

    </div>


</div>

<?php include ('footer.php'); ?>