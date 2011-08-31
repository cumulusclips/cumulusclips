<?php

### Created on February 28, 2009
### Created by Miguel A. Hurtado
### This script displays the site homepage


// Include required files
include ('../cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Video');


// Establish page variables, objects, arrays, etc
Plugin::Trigger ('admin.video_edit.start');
//$logged_in = User::LoginCheck(HOST . '/login/');
//$admin = new User ($logged_in);
$page_title = 'Add Video';
$categories = array();
$data = array();
$errors = array();
$message = null;
$php_path = Settings::Get('php');
$admin_js[] = ADMIN . '/extras/uploadify/swfobject.js';
$admin_js[] = ADMIN . '/extras/uploadify/jquery.uploadify.v2.1.4.min.js';
$admin_js[] = ADMIN . '/js/uploadify.js';
$admin_css[] = ADMIN . '/extras/uploadify/uploadify.css';
$admin_meta['uploadHandler'] = ADMIN . '/videos_add_ajax.php';
$admin_meta['token'] = session_id();
$admin_meta['sizeLimit'] = $config->video_size_limit;
$admin_meta['fileDesc'] = 'Supported Video Formats:';
$admin_meta['fileExt'] = '';
$timestamp = time();
$_SESSION['video_upload_key'] = md5 (md5 ($timestamp) . SECRET_KEY);
$ext = array();



// Generate accepted format strings
foreach ($config->accepted_video_formats as $value) {
    $admin_meta['fileDesc'] .= " (*.$value)";
    $ext[] = "*.$value";
}
$admin_meta['fileExt'] = implode (';', $ext);



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

    ### Validate video upload
    try {

        // Validate timestamp
        if (!empty ($_POST['timestamp']) && is_numeric ($_POST['timestamp'])) {
            $upload_key = md5 (md5 ($_POST['timestamp']) . SECRET_KEY);
        } else {
            throw new Exception ('Invalid timestamp');
        }

        // Verify video AJAX values were set
        if (!empty ($_SESSION['video'])) {
            $video = unserialize ($_SESSION['video']);
        } else {
            throw new Exception ('Invalid video upload');
        }

        // Validate video upload
        if (!is_array ($video)) throw new Exception ('Invalid video upload');
        if (empty ($video['key']) || empty ($video['temp'])) throw new Exception ('Invalid video upload');
        if (!file_exists ($video['temp'])) throw new Exception ('Invalid video upload');
        if ($video['key'] != $upload_key) throw new Exception ('Invalid video upload');

        $_SESSION['video'] = serialize (array ('key' => $_SESSION['video_upload_key'], 'temp' => $video['temp'], 'name' => $video['name']));
        $data['upload'] = $video;

    } catch (Exception $e) {
        $errors['upload'] = $e->getMessage();
    }


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



    // Update video if no errors were made
    if (empty ($errors)) {

        // Create record
//        $data['user_id'] = $user->user_id;
        $data['user_id'] = 1;
        $data['original_extension'] = Functions::GetExtension ($data['upload']['temp']);
        $data['filename'] = basename ($data['upload']['temp'], '.' . $data['original_extension']);
        unset ($data['upload']);
        $data['status'] = 'pending conversion';
        $id = Video::Create ($data);

        // Begin encoding
        $cmd_output = $config->debug_conversion ? CONVERSION_LOG : '/dev/null';
        $converter_cmd = 'nohup ' . $php_path . ' ' . DOC_ROOT . '/cc-core/system/encode.php --video="' . $id . '" >> ' .  $cmd_output . ' &';
        exec ($converter_cmd);

        // Output message
        $message = 'Video has been created.';
        $message_type = 'success';
        unset ($data);
        
    } else {
        $message = 'The following errors were found. Please correct them and try again.';
        $message .= '<br /><br /> - ' . implode ('<br /> - ', $errors);
        $message_type = 'error';
    }

}


// Output Header
include ('header.php');

?>

<div id="videos-add">

    <h1>Add Video</h1>

    <?php if ($message): ?>
    <div class="<?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <div class="block">

        <form action="<?=ADMIN?>/videos_add.php" method="post">

            <div class="row <?=(isset ($errors['upload'])) ? 'errors' : ''?>">
                <label>Video File:</label>
                <div id="upload-box">
                    <input id="browse-button" type="button" class="button" value="Browse" />
                    <input id="upload" type="file" name="upload" />
                    <div class="uploadifyQueue" id="uploadQueue">
                    <?php if (isset ($data['upload'])): ?>
                        <div class="uploadifyQueueItem"><span class="fileName"><?=$data['upload']['name']?> - has been uploaded</span></div>
                    <?php endif; ?>
                    </div>
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
                <input type="hidden" name="timestamp" value="<?=$timestamp?>" id="timestamp" />
                <input type="hidden" name="submitted" value="TRUE" />
                <input type="submit" class="button" value="Add Video" />
            </div>
        </form>

    </div>


</div>

<?php include ('footer.php'); ?>