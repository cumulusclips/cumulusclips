<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$userService = new UserService();
$adminUser = $userService->loginCheck();
Functions::RedirectIf($adminUser, HOST . '/login/');
Functions::RedirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$videoMapper = new VideoMapper();
$videoService = new VideoService();
$page_title = 'Edit Video';
$pageName = 'videos-edit';
$private_url = $videoService->generatePrivate();
$categories = array();
$data = array();
$errors = array();
$message = null;

// Retrieve Category names
$categoryService = new CategoryService();
$categories = $categoryService->getCategories();



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
    if (!$video || !in_array($video->status, array('approved', 'processing', VideoMapper::PENDING_CONVERSION, VideoMapper::PENDING_APPROVAL, 'banned'))) {
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
            $duplicateVideo = $videoMapper->getVideoByCustom(array('private_url' => $_POST['private_url']));
            if ($duplicateVideo && $duplicateVideo->videoId != $video->videoId) throw new Exception ('error');

            // Set private URL
            $video->privateUrl = trim($_POST['private_url']);
            $private_url = $video->privateUrl;

        } catch (Exception $e) {
            $errors['private_url'] = 'Invalid private URL';
        }

    } else {
        $video->private = false;
        $video->privateUrl = null;
    }

    // Validate close comments
    if (!empty ($_POST['closeComments']) && $_POST['closeComments'] == '1') {
        $video->commentsClosed = true;
    } else {
        $video->commentsClosed = false;
    }

    // Validate status
    if (!in_array($video->status, array('processing', VideoMapper::PENDING_CONVERSION))) {
        if (!empty ($_POST['status']) && !ctype_space ($_POST['status'])) {
            $video->status = $newStatus = trim ($_POST['status']);
        } else {
            $errors['status'] = 'Invalid status';
        }
    }


    // Update video if no errors were made
    if (empty ($errors)) {

        // Perform addional actions based on status change
        if (isset($newStatus) && $newStatus != $video->status) {

            // Handle "Approve" action
            if ($newStatus == 'approved') {
                $videoService->Approve ('approve');
            }

            // Handle "Ban" action
            else if ($newStatus == 'banned') {
                $flagService = new FlagService();
                $flagService->flagDecision($video, true);
            }

        }

        $videoMapper->save($video);
        $message = 'Video has been updated.';
        $message_type = 'alert-success';
    } else {
        $message = 'The following errors were found. Please correct them and try again.';
        $message .= '<br /><br /> - ' . implode ('<br /> - ', $errors);
        $message_type = 'alert-danger';
    }

}


// Output Header
include ('header.php');

?>

<h1>Edit Video</h1>

<?php if ($message): ?>
<div class="alert <?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<p><a href="<?=$list_page?>">Return to previous screen</a></p>

<form action="<?=ADMIN?>/videos_edit.php?id=<?=$video->videoId?>" method="post">

    <div class="form-group <?=(isset ($errors['status'])) ? 'has-error' : ''?>">
        <label>Status:</label>
        <?php if (!in_array($video->status, array('processing', VideoMapper::PENDING_CONVERSION))): ?>
            <select name="status" class="form-control">
                <option value="<?= VideoMapper::APPROVED ?>"<?=(isset ($video->status) && $video->status == VideoMapper::APPROVED) ?' selected="selected"':''?>>Approved</option>
                <option value="<?= VideoMapper::PENDING_APPROVAL ?>"<?=(empty ($video->status) || $video->status == VideoMapper::PENDING_APPROVAL) ?' selected="selected"':''?>>Pending</option>
                <option value="<?= VideoMapper::BANNED ?>"<?=(isset ($video->status) && $video->status == VideoMapper::BANNED) ?' selected="selected"':''?>>Banned</option>
            </select>
        <?php else: ?>
            <?=($video->status == 'processing') ? 'Processing' : 'Pending Conversion'?>
        <?php endif; ?>

    </div>

    <div class="form-group <?=(isset ($errors['title'])) ? 'has-error' : ''?>">
        <label class="control-label">Title:</label>
        <input class="form-control" type="text" name="title" value="<?=htmlspecialchars($video->title)?>" />
    </div>

    <div class="form-group <?=(isset ($errors['description'])) ? 'has-error' : ''?>">
        <label class="control-label">Description:</label>
        <textarea rows="7" cols="50" class="form-control" name="description"><?=htmlspecialchars($video->description)?></textarea>
    </div>

    <div class="form-group <?=(isset ($errors['tags'])) ? 'has-error' : ''?>">
        <label class="control-label">Tags:</label>
        <input class="form-control" type="text" name="tags" value="<?=htmlspecialchars(implode (', ', $video->tags))?>" /> (Comma Delimited)
    </div>

    <div class="form-group <?=(isset ($errors['cat_id'])) ? 'has-error' : ''?>">
        <label>Category:</label>
        <select class="form-control" name="cat_id">
        <?php foreach ($categories as $category): ?>
            <option value="<?=$category->categoryId?>"<?=($video->categoryId == $category->categoryId) ? ' selected="selected"' : ''?>><?=$category->name?></option>
        <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group ">
        <input id="disable-embed" type="checkbox" name="disable_embed" value="1" <?=$video->disableEmbed ? 'checked="checked"' : ''?> />
        <label for="disable-embed">Disable Embed</label> <em>(Video cannot be embedded on third party sites)</em>
    </div>

    <div class="form-group ">
        <input id="gated-video" type="checkbox" name="gated" value="1" <?=$video->gated ? 'checked="checked"' : ''?> />
        <label for="gated-video">Gated</label> <em>(Video can only be viewed by members who are logged in)</em>
    </div>

    <div class="form-group ">
        <input id="private-video" data-block="private-url" class="showhide" type="checkbox" name="private" value="1" <?=$video->private ? 'checked="checked"' : ''?> />
        <label for="private-video">Private</label> <em>(Video can only be viewed by you or anyone with the private URL)</em>
    </div>

    <div id="private-url" class="form-group <?=$video->private ? '' : 'hide'?>">
        <label <?=(isset ($errors['private_url'])) ? 'class="error"' : ''?>>Private URL:</label>
        <?=HOST?>/private/videos/<span><?=(!empty ($video->privateUrl)) ? $video->privateUrl : $private_url?></span>/
        <input type="hidden" name="private_url" value="<?=(!empty ($video->privateUrl)) ? $video->privateUrl : $private_url?>" />
        <a href="" class="small">Regenerate</a>
    </div>

    <div class="form-group ">
        <input id="closeComments" type="checkbox" name="closeComments" value="1" <?=$video->commentsClosed ? 'checked="checked"' : ''?> />
        <label for="closeComments">Close Comments</label> <em>(Allow comments to be posted to video)</em>
    </div>

    <input type="hidden" name="submitted" value="TRUE" />
    <input type="submit" class="button" value="Update Video" />
</form>

<?php include ('footer.php'); ?>