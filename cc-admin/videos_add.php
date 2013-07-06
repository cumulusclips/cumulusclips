<?php

// Include required files
include_once(dirname(dirname(__FILE__)) . '/cc-core/config/admin.bootstrap.php');
App::LoadClass('User');
App::LoadClass('Video');

// Establish page variables, objects, arrays, etc
Functions::RedirectIf($logged_in = User::LoginCheck(), HOST . '/login/');
$admin = new User($logged_in);
Functions::RedirectIf(User::CheckPermissions('admin_panel', $admin), HOST . '/myaccount/');
App::EnableUploadsCheck();
$page_title = 'Add Video';
$categories = array();
$data = array();
$errors = array();
$message = null;
$message_type = null;
$php_path = Settings::Get('php');
$admin_js[] = ADMIN . '/extras/uploadify/uploadify.plugin.js';
$admin_js[] = ADMIN . '/js/uploadify.js';
$admin_css[] = ADMIN . '/extras/uploadify/uploadify.css';
$private_url = Video::GeneratePrivate();
$tempFile = null;
$videoUploadMessage = null;
$originalVideoName = null;

// Retrieve Category names
$query = "SELECT cat_id, cat_name FROM " . DB_PREFIX . "categories";
$result = $db->Query($query);
while ($row = $db->FetchObj($result)) {
    $categories[$row->cat_id] = $row->cat_name;
}

// Handle form if submitted
if (isset($_POST['submitted'])) {

    // Validate video upload
    if (!empty($_POST['originalVideoName']) && !empty($_POST['tempFile']) && file_exists($_POST['tempFile'])) {
        $tempFile = $_POST['tempFile'];
        $originalVideoName = trim($_POST['originalVideoName']);
        $videoUploadMessage = $originalVideoName . ' - has been uploaded.';
    } else {
        $errors['video'] = 'Invalid video upload';
    }

    // Validate title
    if (!empty ($_POST['title']) && !ctype_space($_POST['title'])) {
        $data['title'] = trim($_POST['title']);
    } else {
        $errors['title'] = 'Invalid title';
    }

    // Validate description
    if (!empty($_POST['description']) && !ctype_space($_POST['description'])) {
        $data['description'] = trim ($_POST['description']);
    } else {
        $errors['description'] = 'Invalid description';
    }

    // Validate tags
    if (!empty($_POST['tags']) && !ctype_space($_POST['tags'])) {
        $data['tags'] = trim($_POST['tags']);
    } else {
        $errors['tags'] = 'Invalid tags';
    }

    // Validate cat_id
    if (!empty($_POST['cat_id']) && is_numeric($_POST['cat_id'])) {
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
    if (!empty($_POST['gated']) && $_POST['gated'] == '1') {
        $data['gated'] = '1';
    } else {
        $data['gated'] = '0';
    }

    // Validate private
    if (!empty($_POST['private']) && $_POST['private'] == '1') {
        $data['private'] = '1';
        if (!empty($_POST['private_url']) && strlen($_POST['private_url']) == 7 && !Video::Exist(array('private_url' => $_POST['private_url']))) {
            $data['private_url'] = trim($_POST['private_url']);
            $private_url = $data['private_url'];
        } else {
            $errors['private_url'] = 'Invalid private URL';
        }
    } else {
        $data['private'] = '0';
    }

    // Update video if no errors were made
    if (empty($errors)) {

        // Create record
        $data['user_id'] = $admin->user_id;
        $data['original_extension'] = Functions::GetExtension($tempFile);
        $data['filename'] = basename($tempFile, '.' . $data['original_extension']);
        $data['status'] = 'pending conversion';
        $id = Video::Create($data);

        // Begin encoding
        $cmd_output = $config->debug_conversion ? CONVERSION_LOG : '/dev/null';
        $converter_cmd = 'nohup ' . $php_path . ' ' . DOC_ROOT . '/cc-core/system/encode.php --video="' . $id . '" >> ' .  $cmd_output . ' &';
        exec($converter_cmd);

        // Output message
        $tempFile = null;
        $videoUploadMessage = null;
        $originalVideoName = null;
        $message = 'Video has been created.';
        $message_type = 'success';
        unset($data);
        
    } else {
        $message = 'The following errors were found. Please correct them and try again.';
        $message .= '<br /><br /> - ' . implode('<br /> - ', $errors);
        $message_type = 'errors';
    }

}

// Output Header
include('header.php');

?>
<div id="videos-add">

    <h1>Add Video</h1>

    <div class="message <?=$message_type?>"><?=$message?></div>

    <div class="block">

        <form name="uploadify" action="<?=ADMIN?>/videos_add.php" method="post">

            <div class="row <?=(isset ($errors['video'])) ? 'error' : ''?>">
                <label>Video File:</label>
                <input id="upload" type="file" name="upload" />
                <input id="upload_button" class="button" type="button" value="Upload" />
                <input type="hidden" name="uploadLimit" value="<?=$config->video_size_limit?>" />
                <input type="hidden" name="fileTypes" value="<?=htmlspecialchars(json_encode($config->accepted_video_formats))?>" />
                <input type="hidden" name="uploadType" value="video" />
                <input type="hidden" name="originalVideoName" value="<?=htmlspecialchars($originalVideoName)?>" />
                <input type="hidden" name="tempFile" value="<?=$tempFile?>" />
            </div>
            
            <div class="videoUploadComplete"><?=$videoUploadMessage?></div>
            
            <div id="upload_status">
                <div class="title"></div>
                <div class="progress">
                    <a href="" title="Cancel">Cancel</a>
                    <div class="meter">
                        <div class="fill"></div>
                    </div>
                    <div class="percentage">0%</div>
                </div>
            </div>
            
            <div class="row <?=(isset($errors['title'])) ? 'error' : ''?>">
                <label>Title:</label>
                <input class="text" type="text" name="title" value="<?=(isset($data['title'])) ? htmlspecialchars($data['title']) : ''?>" />
            </div>

            <div class="row <?=(isset($errors['description'])) ? 'error' : ''?>">
                <label>Description:</label>
                <textarea rows="7" cols="50" class="text" name="description"><?=(isset($data['description'])) ? htmlspecialchars($data['description']) : ''?></textarea>
            </div>

            <div class="row <?=(isset($errors['tags'])) ? 'error' : ''?>">
                <label>Tags:</label>
                <input class="text" type="text" name="tags" value="<?=(isset($data['tags'])) ? htmlspecialchars($data['tags']) : ''?>" /> (Comma Delimited)
            </div>

            <div class="row <?=(isset($errors['cat_id'])) ? 'error' : ''?>">
                <label>Category:</label>
                <select class="dropdown" name="cat_id">
                <?php foreach ($categories as $cat_id => $cat_name): ?>
                    <option value="<?=$cat_id?>" <?=(isset($data['cat_id']) && $data['cat_id'] == $cat_id) ? '' : 'selected="selected"'?>><?=$cat_name?></option>
                <?php endforeach; ?>
                </select>
            </div>

            <div class="row-shift">
                <input id="disable-embed" type="checkbox" name="disable_embed" value="1" <?=(!empty($errors) && !empty($data['disable_embed'])) ? 'checked="checked"' : ''?> />
                <label for="disable-embed">Disable Embed</label> <em>(Video cannot be embedded on third party sites)</em>
            </div>

            <div class="row-shift">
                <input id="gated-video" type="checkbox" name="gated" value="1" <?=(!empty($errors) && !empty($data['gated'])) ? 'checked="checked"' : ''?> />
                <label for="gated-video">Gated</label> <em>(Video can only be viewed by members who are logged in)</em>
            </div>

            <div class="row-shift">
                <input id="private-video" data-block="private-url" class="showhide" type="checkbox" name="private" value="1" <?=(!empty($errors) && !empty($data['private'])) ? 'checked="checked"' : ''?> />
                <label for="private-video">Private</label> <em>(Video can only be viewed by you or anyone with the private URL below)</em>
            </div>

            <div id="private-url" class="row <?=(isset($errors['private_url'])) ? 'error' : ''?> <?=(!empty($errors) && !empty($data['private'])) ? '' : 'hide'?>">
                <label>Private URL:</label>
                <?=HOST?>/private/videos/<span><?=$private_url?></span>/
                <input type="hidden" name="private_url" value="<?=$private_url?>" />
                <a href="" class="small">Regenerate</a>
            </div>

            <div class="row-shift">
                <input type="hidden" name="submitted" value="TRUE" />
                <input type="submit" class="button" value="Add Video" />
            </div>
        </form>

    </div>


</div>

<?php include('footer.php'); ?>