<?php

// Init view
$view->initView('edit_video');
Plugin::triggerEvent('edit_video.start');

// Verify if user is logged in
$userService = new UserService();
$view->vars->loggedInUser = $userService->loginCheck();
Functions::RedirectIf($view->vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
$videoMapper = new VideoMapper();
$videoService = new VideoService();
$page_title = 'Edit Video';
$private_url = $videoService->generatePrivate();
$categories = array();
$data = array();
$errors = array();
$message = null;

// Retrieve Category names
$query = "SELECT category_id, name FROM " . DB_PREFIX . "categories";
$result = $db->fetchAll($query);
foreach ($result as $row) {
    $categories[$row['category_id']] = $row['name'];
}



// Build return to list link
if (!empty ($_SESSION['list_page'])) {
    $list_page = $_SESSION['list_page'];
} else {
    $list_page = ADMIN . '/videos.php';
}



### Verify a video was provided
if (!empty($_GET['id']) && is_numeric ($_GET['id']) && $_GET['id'] > 0) {

    ### Retrieve video information
    $video = $videoMapper->getVideoById($_GET['id']);
    if (!$video || !in_array($video->status, array('approved', 'processing', 'pendingConversion', 'pendingApproval', 'banned'))) {
        header ('Location: ' . ADMIN . '/videos.php');
        exit();
    }
    if ($video->private) $private_url = $video->privateUrl;
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
        $video->title = trim($_POST['title']);
    } else {
        $errors['title'] = 'Invalid title';
    }


    // Validate description
    if (!empty ($_POST['description']) && !ctype_space ($_POST['description'])) {
        $video->description = trim ($_POST['description']);
    } else {
        $errors['description'] = 'Invalid description';
    }


    // Validate tags
    if (!empty ($_POST['tags']) && !ctype_space ($_POST['tags'])) {
        $video->tags = preg_split('/,\s*/', trim($_POST['tags']));
    } else {
        $errors['tags'] = 'Invalid tags';
    }


    // Validate cat_id
    if (!empty ($_POST['cat_id']) && is_numeric ($_POST['cat_id'])) {
        $video->categoryId = $_POST['cat_id'];
    } else {
        $errors['cat_id'] = 'Invalid category';
    }


    // Validate disable embed
    if (!empty ($_POST['disable_embed']) && $_POST['disable_embed'] == '1') {
        $video->disableEmbed = true;
    } else {
        $video->disableEmbed = false;
    }


    // Validate gated
    if (!empty ($_POST['gated']) && $_POST['gated'] == '1') {
        $video->gated = true;
    } else {
        $video->gated = false;
    }


    // Validate private
    if (!empty ($_POST['private']) && $_POST['private'] == '1') {
        $video->private = true;

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
    if (!in_array($video->status, array('processing', 'pendingConversion'))) {
        if (!empty ($_POST['status']) && !ctype_space ($_POST['status'])) {
            $data['status'] = htmlspecialchars (trim ($_POST['status']));
        } else {
            $errors['status'] = 'Invalid status';
        }
    }


    // Update video if no errors were made
    if (empty ($errors)) {

        // Perform addional actions based on status change
        if (isset($data['status']) && $data['status'] != $video->status) {

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
        $message_type = 'errors';
    }

}


// Output Header
include ('header.php');

?>

<div id="videos-edit">

    <h1>Edit Video</h1>

    <?php if ($message): ?>
    <div class="message <?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <div class="block">

        <p><a href="<?=$list_page?>">Return to previous screen</a></p>

        <form action="<?=ADMIN?>/videos_edit.php?id=<?=$video->videoId?>" method="post">

            <div class="row<?=(isset ($errors['status'])) ? ' error' : ''?>">
                <label>Status:</label>
                <?php if (!in_array($video->status, array('processing', 'pendingConversion'))): ?>
                    <select name="status" class="dropdown">
                        <option value="approved"<?=(isset ($video->status) && $video->status == 'approved') || (!isset ($video->status) && $video->status == 'approved')?' selected="selected"':''?>>Approved</option>
                        <option value="pendingApproval"<?=(isset ($video->status) && $video->status == 'pendingApproval') || (!isset ($video->status) && $video->status == 'pendingApproval')?' selected="selected"':''?>>Pending</option>
                        <option value="banned"<?=(isset ($video->status) && $video->status == 'banned') || (!isset ($video->status) && $video->status == 'banned')?' selected="selected"':''?>>Banned</option>
                    </select>
                <?php else: ?>
                    <?=($video->status == 'processing') ? 'Processing' : 'Pending Conversion'?>
                <?php endif; ?>

            </div>

            <div class="row<?=(isset ($errors['title'])) ? ' error' : ''?>">
                <label>Title:</label>
                <input class="text" type="text" name="title" value="<?=(!empty ($errors) && isset ($video->title)) ? $video->title : $video->title?>" />
            </div>

            <div class="row<?=(isset ($errors['description'])) ? ' error' : ''?>">
                <label>Description:</label>
                <textarea rows="7" cols="50" class="text" name="description"><?=(!empty ($errors) && isset ($video->description)) ? $video->description : $video->description?></textarea>
            </div>

            <div class="row<?=(isset ($errors['tags'])) ? ' error' : ''?>">
                <label>Tags:</label>
                <input class="text" type="text" name="tags" value="<?=(!empty ($errors) && isset ($video->tags)) ? $video->tags : implode (', ', $video->tags)?>" /> (Comma Delimited)
            </div>

            <div class="row<?=(isset ($errors['cat_id'])) ? ' error' : ''?>">
                <label>Category:</label>
                <select class="dropdown" name="cat_id">
                <?php foreach ($categories as $cat_id => $cat_name): ?>
                    <option value="<?=$cat_id?>"<?=(isset ($video->categoryd) && $video->cat_id == $cat_id) || (!isset ($video->cat_id) && $video->cat_id == $cat_id) ? ' selected="selected"' : ''?>><?=$cat_name?></option>
                <?php endforeach; ?>
                </select>
            </div>

            <div class="row-shift">
                <input id="disable-embed" type="checkbox" name="disable_embed" value="1" <?=(!empty ($errors)) ? ($video->disableEmbed ? 'checked="checked"' : '') : ($video->disableEmbed ? 'checked="checked"' : '')?> />
                <label for="disable-embed">Disable Embed</label> <em>(Video cannot be embedded on third party sites)</em>
            </div>

            <div class="row-shift">
                <input id="gated-video" type="checkbox" name="gated" value="1" <?=(!empty ($errors)) ? ($video->gated ? 'checked="checked"' : '') : ($video->gated ? 'checked="checked"' : '')?> />
                <label for="gated-video">Gated</label> <em>(Video can only be viewed by members who are logged in)</em>
            </div>

            <div class="row-shift">
                <input id="private-video" data-block="private-url" class="showhide" type="checkbox" name="private" value="1" <?=(!empty ($errors)) ? ($video->private ? 'checked="checked"' : '') : ($video->private ? 'checked="checked"' : '')?> />
                <label for="private-video">Private</label> <em>(Video can only be viewed by you or anyone with the private URL below)</em>
            </div>

            <div id="private-url" class="row <?=(!empty ($errors)) ? ($video->private ? '' : 'hide') : ($video->private ? '' : 'hide')?>">

                <label <?=(isset ($errors['private_url'])) ? 'class="error"' : ''?>>Private URL:</label>
                <?=HOST?>/private/videos/<span><?=(!empty ($errors) && !empty ($video->privateUrl)) ? $video->privateUrl : $private_url?></span>/

                <input type="hidden" name="private_url" value="<?=(!empty ($errors) && !empty ($video->privateUrl)) ? $video->privateUrl : $private_url?>" />
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