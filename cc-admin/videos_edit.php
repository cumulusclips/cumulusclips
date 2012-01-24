<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');
App::LoadClass ('Flag');


// Establish page variables, objects, arrays, etc
Functions::RedirectIf ($logged_in = User::LoginCheck(), HOST . '/login/');
$admin = new User ($logged_in);
Functions::RedirectIf (User::CheckPermissions ('admin_panel', $admin), HOST . '/myaccount/');
$page_title = 'Edit Video';
$private_url = Video::GeneratePrivate();
$categories = array();
$data = array();
$errors = array();
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
    if ($video->private == '1') $private_url = $video->private_url;
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


    // Validate disable embed
    if (!empty ($_POST['disable_embed']) && $_POST['disable_embed'] == '1') {
        $data['disable_embed'] = '1';
    } else {
        $data['disable_embed'] = '0';
    }


    // Validate gated
    if (!empty ($_POST['gated']) && $_POST['gated'] == '1') {
        $data['gated'] = '1';
    } else {
        $data['gated'] = '0';
    }


    // Validate private
    if (!empty ($_POST['private']) && $_POST['private'] == '1') {
        $data['private'] = '1';

        try {

            // Validate private URL
            if (empty ($_POST['private_url'])) throw new Exception ('error');
            if (strlen ($_POST['private_url']) != 7) throw new Exception ('error');
            $vid = Video::Exist (array ('private_url' => $_POST['private_url']));
            if ($vid && $vid != $video->video_id) throw new Exception ('error');

            // Set private URL
            $data['private_url'] = htmlspecialchars (trim ($_POST['private_url']));
            $private_url = $data['private_url'];

        } catch (Exception $e) {
            $errors['private_url'] = 'Invalid private URL';
        }

    } else {
        $data['private'] = '0';
        $data['private_url'] = '';
        $private_url = Video::GeneratePrivate();
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
                $video->Approve ('approve');
            }

            // Handle "Ban" action
            else if ($data['status'] == 'banned') {
                Flag::FlagDecision ($video->video_id, 'video', true);
            }

        }

        $video->Update ($data);
        $message = 'Video has been updated.';
        $message_type = 'success';
    } else {
        $message = 'The following errors were found. Please correct them and try again.';
        $message .= '<br /><br /> - ' . implode ('<br /> - ', $errors);
        $message_type = 'error';
    }

}


// Output Header
include ('header.php');

?>

<div id="videos-edit">

    <h1>Edit Video</h1>

    <?php if ($message): ?>
    <div class="<?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <div class="block">

        <p><a href="<?=$list_page?>">Return to previous screen</a></p>

        <form action="<?=ADMIN?>/videos_edit.php?id=<?=$video->video_id?>" method="post">

            <div class="row<?=(isset ($errors['status'])) ? ' errors' : ''?>">
                <label>Status:</label>
                <select name="status" class="dropdown">
                    <option value="approved"<?=(isset ($data['status']) && $data['status'] == 'approved') || (!isset ($data['status']) && $video->status == 'approved')?' selected="selected"':''?>>Approved</option>
                    <option value="pending approval"<?=(isset ($data['status']) && $data['status'] == 'pending approval') || (!isset ($data['status']) && $video->status == 'pending approval')?' selected="selected"':''?>>Pending</option>
                    <option value="banned"<?=(isset ($data['status']) && $data['status'] == 'banned') || (!isset ($data['status']) && $video->status == 'banned')?' selected="selected"':''?>>Banned</option>
                </select>
            </div>

            <div class="row<?=(isset ($errors['title'])) ? ' errors' : ''?>">
                <label>Title:</label>
                <input class="text" type="text" name="title" value="<?=(!empty ($errors) && isset ($data['title'])) ? $data['title'] : $video->title?>" />
            </div>

            <div class="row<?=(isset ($errors['description'])) ? ' errors' : ''?>">
                <label>Description:</label>
                <textarea rows="7" cols="50" class="text" name="description"><?=(!empty ($errors) && isset ($data['description'])) ? $data['description'] : $video->description?></textarea>
            </div>

            <div class="row<?=(isset ($errors['tags'])) ? ' errors' : ''?>">
                <label>Tags:</label>
                <input class="text" type="text" name="tags" value="<?=(!empty ($errors) && isset ($data['tags'])) ? $data['tags'] : implode (', ', $video->tags)?>" /> (Comma Delimited)
            </div>

            <div class="row<?=(isset ($errors['cat_id'])) ? ' errors' : ''?>">
                <label>Category:</label>
                <select class="dropdown" name="cat_id">
                <?php foreach ($categories as $cat_id => $cat_name): ?>
                    <option value="<?=$cat_id?>"<?=(isset ($data['cat_id']) && $data['cat_id'] == $cat_id) || (!isset ($data['cat_id']) && $video->cat_id == $cat_id) ? ' selected="selected"' : ''?>><?=$cat_name?></option>
                <?php endforeach; ?>
                </select>
            </div>

            <div class="row-shift">
                <input id="disable-embed" type="checkbox" name="disable_embed" value="1" <?=(!empty ($errors)) ? ($data['disable_embed'] == '1' ? 'checked="checked"' : '') : ($video->disable_embed == '1' ? 'checked="checked"' : '')?> />
                <label for="disable-embed">Disable Embed</label> <em>(Video cannot be embedded on third party sites)</em>
            </div>

            <div class="row-shift">
                <input id="gated-video" type="checkbox" name="gated" value="1" <?=(!empty ($errors)) ? ($data['gated'] == '1' ? 'checked="checked"' : '') : ($video->gated == '1' ? 'checked="checked"' : '')?> />
                <label for="gated-video">Gated</label> <em>(Video can only be viewed by members who are logged in)</em>
            </div>

            <div class="row-shift">
                <input id="private-video" data-block="private-url" class="showhide" type="checkbox" name="private" value="1" <?=(!empty ($errors)) ? ($data['private'] == '1' ? 'checked="checked"' : '') : ($video->private == '1' ? 'checked="checked"' : '')?> />
                <label for="private-video">Private</label> <em>(Video can only be viewed by you or anyone with the private URL below)</em>
            </div>

            <div id="private-url" class="row <?=(!empty ($errors)) ? ($data['private'] == '1' ? '' : 'hide') : ($video->private == '1' ? '' : 'hide')?>">

                <label <?=(isset ($errors['private_url'])) ? 'class="errors"' : ''?>>Private URL:</label>
                <?=HOST?>/private/videos/<span><?=(!empty ($errors) && !empty ($data['private_url'])) ? $data['private_url'] : $private_url?></span>/

                <input type="hidden" name="private_url" value="<?=(!empty ($errors) && !empty ($data['private_url'])) ? $data['private_url'] : $private_url?>" />
                <a href="" class="small">Regenerate</a>
            </div>

            <div class="row-shift">
                <input type="hidden" name="submitted" value="TRUE" />
                <input type="submit" class="button" value="Update Video" />
            </div>
        </form>

    </div>


</div>

<?php include ('footer.php'); ?>