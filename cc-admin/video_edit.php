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
$content = 'video_edit.tpl';
$page_title = 'Edit Video';
$categories = array();
$data = array();
$Errors = array();
$message = null;



// Retrieve Category names
$query = "SELECT cat_id, cat_name FROM " . DB_PREFIX . "categories";
$result = $db->Query ($query);
while ($row = $db->FetchObj ($result)) {
    $categories[$row->cat_id] = $row->cat_name;
}



// Build return to list link
if (!empty ($_SESSION['list_page'])) {
    $list_page = $_SESSION['list_page'];
} else {
    $list_page = ADMIN . '/videos.php';
}



### Verify a video was provided
if (isset ($_GET['id']) && is_numeric ($_GET['id']) && $_GET['id'] != 0) {

    ### Retrieve video information
    $video = new Video ($_GET['id']);
    if (!$video->found) {
        header ('Location: ' . ADMIN . '/videos.php');
        exit();
    }

} else {
    header ('Location: ' . ADMIN . '/videos.php');
    exit();
}





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

<div id="video-edit">

    <h1><?=Language::GetText('update_video_header')?></h1>

    <?php if ($message): ?>
    <div class="<?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <div class="block">

        <p><a href="<?=$list_page?>">Return to previous screen</a></p>

        <form action="<?=ADMIN?>/video_edit.php?id=<?=$video->video_id?>" method="post">

            <div class="row<?=(isset ($Errors['status'])) ? ' errors' : ''?>">
                <label>Status:</label>
                <select name="status" class="dropdown">
                    <option value="approved"<?=(isset ($data['status']) && $data['status'] == 'approved') || (!isset ($data['status']) && $video->status == 'approved')?' selected="selected"':''?>>Approved</option>
                    <option value="pending"<?=(isset ($data['status']) && $data['status'] == 'pending') || (!isset ($data['status']) && $video->status == 'pending')?' selected="selected"':''?>>Pending</option>
                    <option value="banned"<?=(isset ($data['status']) && $data['status'] == 'banned') || (!isset ($data['status']) && $video->status == 'banned')?' selected="selected"':''?>>Banned</option>
                </select>
            </div>

            <div class="row<?=(isset ($Errors['title'])) ? ' errors' : ''?>">
                <label>*<?=Language::GetText('title')?>:</label>
                <input class="text" type="text" name="title" value="<?=(!empty ($Errors) && isset ($data['title'])) ? $data['title'] : $video->title?>" />
            </div>

            <div class="row<?=(isset ($Errors['description'])) ? ' errors' : ''?>">
                <label>*<?=Language::GetText('description')?>:</label>
                <textarea rows="7" cols="50" class="text" name="description"><?=(!empty ($Errors) && isset ($data['description'])) ? $data['description'] : $video->description?></textarea>
            </div>

            <div class="row<?=(isset ($Errors['tags'])) ? ' errors' : ''?>">
                <label>*<?=Language::GetText('tags')?>:</label>
                <input class="text" type="text" name="tags" value="<?=(!empty ($Errors) && isset ($data['tags'])) ? $data['tags'] : implode (', ', $video->tags)?>" /> <em>*<?=Language::GetText('comma_delimited')?></em>
            </div>

            <div class="row<?=(isset ($Errors['cat_id'])) ? ' errors' : ''?>">
                <label>*<?=Language::GetText('category')?>:</label>
                <select class="dropdown" name="cat_id">
                <?php foreach ($categories as $cat_id => $cat_name): ?>
                    <option value="<?=$cat_id?>"<?=(isset ($data['cat_id']) && $data['cat_id'] == $cat_id) || (!isset ($data['cat_id']) && $video->cat_id == $cat_id) ? ' selected="selected"' : ''?>><?=$cat_name?></option>
                <?php endforeach; ?>
                </select>
            </div>

            <div class="row-shift">
                <input type="hidden" name="submitted" value="TRUE" />
                <input type="submit" class="button" value="<?=Language::GetText('update_video_button')?>" />
            </div>
        </form>

    </div>


</div>

<?php include ('footer.php'); ?>