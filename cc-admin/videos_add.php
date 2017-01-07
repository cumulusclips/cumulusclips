<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$userService = new UserService();
$adminUser = $userService->loginCheck();
Functions::RedirectIf($adminUser, HOST . '/login/');
Functions::RedirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/account/');
App::enableUploadsCheck();

// Establish page variables, objects, arrays, etc
$videoMapper = new VideoMapper();
$videoService = new VideoService();
$video = new Video();
$page_title = 'Add Video';
$categories = array();
$data = array();
$errors = array();
$message = null;
$message_type = null;
$admin_js[] = ADMIN . '/extras/fileupload/fileupload.jquery-ui.widget.js';
$admin_js[] = ADMIN . '/extras/fileupload/fileupload.iframe-transport.js';
$admin_js[] = ADMIN . '/extras/fileupload/fileupload.plugin.js';
$admin_js[] = ADMIN . '/js/fileupload.js';
$private_url = $videoService->generatePrivate();
$prepopulate = null;

// Retrieve Category names
$categoryService = new CategoryService();
$categories = $categoryService->getCategories();

// Handle form if submitted
if (isset($_POST['submitted'])) {

    // Validate file upload
    if (
        !empty($_POST['upload']['original-name'])
        && !empty($_POST['upload']['original-size'])
        && !empty($_POST['upload']['temp'])
        && \App::isValidUpload($_POST['upload']['temp'], $adminUser, 'video')
    ) {
        $prepopulate = urlencode(json_encode(array(
            'path' => $_POST['upload']['temp'],
            'name' => trim($_POST['upload']['original-name']),
            'size' => trim($_POST['upload']['original-size'])
        )));
    } else {
        $errors['video'] = 'Invalid video upload';
    }

    // Validate title
    if (!empty ($_POST['title']) && !ctype_space($_POST['title'])) {
        $video->title = trim($_POST['title']);
    } else {
        $errors['title'] = 'Invalid title';
    }

    // Validate description
    if (!empty($_POST['description']) && !ctype_space($_POST['description'])) {
        $video->description = trim ($_POST['description']);
    } else {
        $errors['description'] = 'Invalid description';
    }

    // Validate tags
    if (!empty($_POST['tags']) && !ctype_space($_POST['tags'])) {
        $video->tags = preg_split('/,\s*/', trim($_POST['tags']));
    } else {
        $errors['tags'] = 'Invalid tags';
    }

    // Validate cat_id
    if (!empty($_POST['cat_id']) && is_numeric($_POST['cat_id'])) {
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
    if (!empty($_POST['gated']) && $_POST['gated'] == '1') {
        $video->gated = true;
    } else {
        $video->gated = false;
    }

    // Validate private
    if (!empty($_POST['private']) && $_POST['private'] == '1') {
        $video->private = true;
        if (!empty($_POST['private_url']) && strlen($_POST['private_url']) == 7 && !$videoMapper->getVideoByCustom(array('private_url' => $_POST['private_url']))) {
            $video->privateUrl = trim($_POST['private_url']);
            $private_url = $video->privateUrl;
        } else {
            $errors['private_url'] = 'Invalid private URL';
        }
    } else {
        $video->private = false;
    }

    // Validate close comments
    if (!empty($_POST['closeComments']) && $_POST['closeComments'] == '1') {
        $video->commentsClosed = true;
    } else {
        $video->commentsClosed = false;
    }

    // Update video if no errors were made
    if (empty($errors)) {

        try {
            // Retrieve video filename and extension
            $video->filename = $videoService->generateFilename();
            $video->originalExtension = Functions::getExtension($_POST['upload']['temp']);

            // Create record
            $video->userId = $adminUser->userId;
            $video->status = VideoMapper::PENDING_CONVERSION;
            $videoId = $videoMapper->save($video);

            // Rename temp video file
            Filesystem::rename(
                $_POST['upload']['temp'],
                UPLOAD_PATH . '/temp/' . $video->filename . '.' . $video->originalExtension
            );

            // Begin transcoding
            $commandOutput = $config->debugConversion ? CONVERSION_LOG : '/dev/null';
            $command = 'nohup ' . Settings::get('php') . ' ' . DOC_ROOT . '/cc-core/system/encode.php --video="' . $videoId . '" >> ' .  $commandOutput . ' 2>&1 &';
            exec($command);

            // Output message
            $prepopulate = null;
            $message = 'Video has been created.';
            $message_type = 'alert-success';
            $video = null;
        } catch (Exception $exception) {
            $message = $exception->getMessage();
            $message_type = 'alert-danger';
        }

    } else {
        $message = 'The following errors were found. Please correct them and try again.';
        $message .= '<br /><br /> - ' . implode('<br /> - ', $errors);
        $message_type = 'alert-danger';
    }
}

// Output Header
$pageName = 'videos-add';
include('header.php');

?>

<h1>Add Video</h1>

<div class="alert <?=$message_type?>"><?=$message?></div>

<form action="<?php echo ADMIN; ?>/videos_add.php" method="post">

    <div class="form-group select-file <?=(isset ($errors['video'])) ? 'has-error' : ''?>">
        <label class="control-label">Video File:</label>
        <input
            class="uploader"
            type="file"
            name="upload"
            data-url="<?php echo BASE_URL; ?>/ajax/upload/"
            data-text="<?php echo Language::getText('browse_files_button'); ?>"
            data-limit="<?php echo $config->videoSizeLimit; ?>"
            data-extensions="<?php echo urlencode(json_encode($config->acceptedVideoFormats)); ?>"
            data-prepopulate="<?php echo urlencode(json_encode($prepopulate)); ?>"
            data-type="video"
        />
    </div>

    <div class="form-group <?=(isset($errors['title'])) ? 'has-error' : ''?>">
        <label class="control-label">Title:</label>
        <input class="form-control" type="text" name="title" value="<?=(!empty($video->title)) ? htmlspecialchars($video->title) : ''?>" />
    </div>

    <div class="form-group <?=(isset($errors['description'])) ? 'has-error' : ''?>">
        <label class="control-label">Description:</label>
        <textarea rows="7" cols="50" class="form-control" name="description"><?=(!empty($video->description)) ? htmlspecialchars($video->description) : ''?></textarea>
    </div>

    <div class="form-group <?=(isset($errors['tags'])) ? 'has-error' : ''?>">
        <label class="control-label">Tags:</label>
        <input class="form-control" type="text" name="tags" value="<?=(!empty($video->tags)) ? htmlspecialchars(implode(', ', $video->tags)) : ''?>" /> (Comma Delimited)
    </div>

    <div class="form-group <?=(isset($errors['cat_id'])) ? 'has-error' : ''?>">
        <label class="control-label">Category:</label>
        <select class="form-control" name="cat_id">
        <?php foreach ($categories as $category): ?>
            <option value="<?=$category->categoryId?>" <?=(!empty($video->categoryId) && $video->categoryId == $category->categoryId) ? '' : 'selected="selected"'?>><?=$category->name?></option>
        <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <input id="disable-embed" type="checkbox" name="disable_embed" value="1" <?=(!empty($video->disableEmbed)) ? 'checked="checked"' : ''?> />
        <label for="disable-embed">Disable Embed</label> <em>(Video cannot be embedded on third party sites)</em>
    </div>

    <div class="form-group">
        <input id="gated-video" type="checkbox" name="gated" value="1" <?=(!empty($video->gated)) ? 'checked="checked"' : ''?> />
        <label for="gated-video">Gated</label> <em>(Video can only be viewed by members who are logged in)</em>
    </div>

    <div class="form-group">
        <input id="private-video" data-block="private-url" class="showhide" type="checkbox" name="private" value="1" <?=(!empty($errors) && !empty($video->private)) ? 'checked="checked"' : ''?> />
        <label for="private-video">Private</label> <em>(Video can only be viewed by you or anyone with the private URL)</em>
    </div>

    <div id="private-url" class="form-group <?=(isset($errors['private_url'])) ? 'has-error' : ''?> <?=(!empty($video->private)) ? '' : 'hide'?>">
        <label class="control-label">Private URL:</label>
        <?=HOST?>/private/videos/<span><?=$private_url?></span>/
        <input type="hidden" name="private_url" value="<?=$private_url?>" />
        <a href="" class="small">Regenerate</a>
    </div>

    <div class="form-group">
        <input id="closeComments" type="checkbox" name="closeComments" value="1" <?=(!empty($video->commentsClosed)) ? 'checked="checked"' : ''?> />
        <label for="closeComments">Close Comments</label> <em>(Allow comments to be posted to video)</em>
    </div>

    <input type="hidden" name="submitted" value="TRUE" />
    <input type="submit" class="button" value="Add Video" />

</form>

<?php include('footer.php'); ?>