<?php

### Created on February 28, 2009
### Created by Miguel A. Hurtado
### This script displays the site homepage


// Include required files
include ('../cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');


// Establish page variables, objects, arrays, etc
Plugin::Trigger ('admin.video_edit.start');
//$logged_in = User::LoginCheck(HOST . '/login/');
//$admin = new User ($logged_in);
$page_title = 'Video Settings';
$data = array();
$errors = array();
$message = null;


$data['enable_uploads'] = Settings::Get('enable_uploads');
$data['debug_conversion'] = Settings::Get('debug_conversion');
$data['video_size_limit'] = Settings::Get('video_size_limit');
$data['accepted_video_formats'] = implode (', ', unserialize (Settings::Get('accepted_video_formats')));
$data['h264_url'] = Settings::Get('h264_url');
$data['theora_url'] = Settings::Get('theora_url');
$data['mobile_url'] = Settings::Get('mobile_url');
$data['thumb_url'] = Settings::Get('thumb_url');
$data['h264_options'] = Settings::Get('h264_options');
$data['theora_options'] = Settings::Get('theora_options');
$data['mobile_options'] = Settings::Get('mobile_options');
$data['thumb_options'] = Settings::Get('thumb_options');





/***********************
Handle form if submitted
***********************/

if (isset ($_POST['submitted'])) {

    // Validate sitename
    if (!empty ($_POST['sitename']) && !ctype_space ($_POST['sitename'])) {
        $data['sitename'] = htmlspecialchars (trim ($_POST['sitename']));
    } else {
        $errors['sitename'] = 'Invalid sitename';
    }


    // Validate base_url
    $pattern = '/^https?:\/\/[a-z0-9][a-z0-9\.\-]+.*$/i';
    if (!empty ($_POST['base_url']) && preg_match ($pattern, $_POST['base_url'])) {
        $data['base_url'] = htmlspecialchars (trim ($_POST['base_url']));
    } else {
        $errors['base_url'] = 'Invalid base url';
    }


    // Validate admin_email
    $pattern = '/^[a-z0-9][a-z0-9\.\-]+@[a-z0-9][a-z0-9\.\-]+$/i';
    if (!empty ($_POST['admin_email']) && preg_match ($pattern, $_POST['admin_email'])) {
        $data['admin_email'] = htmlspecialchars (trim ($_POST['admin_email']));
    } else {
        $errors['admin_email'] = 'Invalid admin email';
    }


    // Validate php path
    if (!empty ($_POST['php']) && !ctype_space ($_POST['php'])) {
        $data['php'] = htmlspecialchars (trim ($_POST['php']));
    } else {
        $errors['php'] = 'Invalid path to php';
    }


    // Validate ffmpeg path
    if (!empty ($_POST['ffmpeg']) && !ctype_space ($_POST['ffmpeg'])) {
        $data['ffmpeg'] = htmlspecialchars (trim ($_POST['ffmpeg']));
    } else {
        $errors['ffmpeg'] = 'Invalid path to ffmpeg';
    }


    // Validate auto_approve_videos
    if (isset ($_POST['auto_approve_videos']) && in_array ($_POST['auto_approve_videos'], array ('1', '0'))) {
        $data['auto_approve_videos'] = $_POST['auto_approve_videos'];
    } else {
        $errors['auto_approve_videos'] = 'Invalid video approval option';
    }


    // Validate auto_approve_users
    if (isset ($_POST['auto_approve_users']) && in_array ($_POST['auto_approve_users'], array ('1', '0'))) {
        $data['auto_approve_users'] = $_POST['auto_approve_users'];
    } else {
        $errors['auto_approve_users'] = 'Invalid member approval option';
    }


    // Validate auto_approve_comments
    if (isset ($_POST['auto_approve_comments']) && in_array ($_POST['auto_approve_comments'], array ('1', '0'))) {
        $data['auto_approve_comments'] = $_POST['auto_approve_comments'];
    } else {
        $errors['auto_approve_comments'] = 'Invalid comment approval option';
    }



    // Update video if no errors were made
    if (empty ($errors)) {
        foreach ($data as $key => $value) {
            Settings::Set ($key, $value);
        }
        $message = 'Settings have been updated.';
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

<div id="settings-video">

    <h1>Video Settings</h1>

    <?php if ($message): ?>
    <div class="<?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <div class="block">

        <form action="<?=ADMIN?>/settings.php" method="post">

            <div class="row <?=(isset ($errors['enable_uploads'])) ? ' errors' : ''?>">
                <label>Video Uploads:</label>
                <select name="enable_uploads" class="dropdown">
                    <option value="1" <?=($data['enable_uploads']=='1')?'selected="selected"':''?>>Enabled</option>
                    <option value="0" <?=($data['enable_uploads']=='0')?'selected="selected"':''?>>Disabled</option>
                </select>
            </div>

            <div class="row <?=(isset ($errors['debug_conversion'])) ? ' errors' : ''?>">
                <label>Log Encoding:</label>
                <select name="debug_conversion" class="dropdown">
                    <option value="1" <?=($data['debug_conversion']=='1')?'selected="selected"':''?>>On</option>
                    <option value="0" <?=($data['debug_conversion']=='0')?'selected="selected"':''?>>Off</option>
                </select>
            </div>
            
            <div class="row <?=(isset ($errors['accepted_video_formats'])) ? ' errors' : ''?>">
                <label>Accepted Video Formats:</label>
                <input class="text" type="text" name="accepted_video_formats" value="<?=$data['accepted_video_formats']?>" />
                (comma delimited)
            </div>

            <div class="row <?=(isset ($errors['video_size_limit'])) ? ' errors' : ''?>">
                <label>Video Site Limit:</label>
                <input class="text" type="text" name="video_size_limit" value="<?=$data['video_size_limit']?>" />
                (bytes)
            </div>

            <div class="row <?=(isset ($errors['h264_url'])) ? ' errors' : ''?>">
                <label>h.264 (MP4) Video URL:</label>
                <input class="text" type="text" name="h264_url" value="<?=$data['h264_url']?>" />
            </div>

            <div class="row <?=(isset ($errors['theora_url'])) ? ' errors' : ''?>">
                <label>Theora (OGG) Video URL:</label>
                <input class="text" type="text" name="theora_url" value="<?=$data['theora_url']?>" />
            </div>

            <div class="row <?=(isset ($errors['mobile_url'])) ? ' errors' : ''?>">
                <label>Mobile Video URL:</label>
                <input class="text" type="text" name="mobile_url" value="<?=$data['mobile_url']?>" />
            </div>

            <div class="row <?=(isset ($errors['thumb_url'])) ? ' errors' : ''?>">
                <label>Thumbnail URL:</label>
                <input class="text" type="text" name="thumb_url" value="<?=$data['thumb_url']?>" />
            </div>

            <div class="row <?=(isset ($errors['h264_options'])) ? ' errors' : ''?>">
                <label>h.264 Options:</label>
                <input class="text" type="text" name="h264_options" value="<?=$data['h264_options']?>" />
            </div>

            <div class="row <?=(isset ($errors['theora_options'])) ? ' errors' : ''?>">
                <label>Theora Options:</label>
                <input class="text" type="text" name="theora_options" value="<?=$data['theora_options']?>" />
            </div>

            <div class="row <?=(isset ($errors['mobile_options'])) ? ' errors' : ''?>">
                <label>Mobile Options:</label>
                <input class="text" type="text" name="mobile_options" value="<?=$data['mobile_options']?>" />
            </div>

            <div class="row <?=(isset ($errors['thumb_options'])) ? ' errors' : ''?>">
                <label>Thumbnail Options:</label>
                <input class="text" type="text" name="thumb_options" value="<?=$data['thumb_options']?>" />
            </div>

            <div class="row-shift">
                <input type="hidden" name="submitted" value="TRUE" />
                <input type="submit" class="button" value="Update Settings" />
            </div>
        </form>

    </div>


</div>

<?php include ('footer.php'); ?>