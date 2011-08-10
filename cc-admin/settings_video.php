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
$data['accepted_video_formats_string'] = implode (', ', unserialize (Settings::Get('accepted_video_formats')));
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

    // Validate enable uploads setting
    if (isset ($_POST['enable_uploads']) && in_array ($_POST['enable_uploads'], array ('1', '0'))) {
        $data['enable_uploads'] = $_POST['enable_uploads'];
    } else {
        $errors['enable_uploads'] = 'Invalid upload enablement option';
    }


    // Validate log encoding setting
    if (isset ($_POST['debug_conversion']) && in_array ($_POST['debug_conversion'], array ('1', '0'))) {
        $data['debug_conversion'] = $_POST['debug_conversion'];
    } else {
        $errors['debug_conversion'] = 'Invalid encoding log option';
    }


    // Validate video size limit
    if (!empty ($_POST['video_size_limit']) && is_numeric ($_POST['video_size_limit'])) {
        $data['video_size_limit'] = trim ($_POST['video_size_limit']);
    } else {
        $errors['video_size_limit'] = 'Invalid video size limit';
    }


    // Validate accepted video formats
    if (!empty ($_POST['accepted_video_formats']) && !ctype_space ($_POST['accepted_video_formats'])) {
        $data['accepted_video_formats_string'] = htmlspecialchars (trim ($_POST['accepted_video_formats']));
        $formats = preg_split ('/,\s?/', $data['accepted_video_formats_string']);
        $data['accepted_video_formats'] = serialize ($formats);
    } else {
        $errors['accepted_video_formats'] = 'Invalid video formats';
    }


    // Validate h.264 video url
    if (!empty ($_POST['h264_url']) && !ctype_space ($_POST['h264_url'])) {
        $data['h264_url'] = htmlspecialchars (trim ($_POST['h264_url']));
    } else {
        $errors['h264_url'] = 'Invalid h.264 video url';
    }


    // Validate theora video url
    if (!empty ($_POST['theora_url']) && !ctype_space ($_POST['theora_url'])) {
        $data['theora_url'] = htmlspecialchars (trim ($_POST['theora_url']));
    } else {
        $errors['theora_url'] = 'Invalid theora video url';
    }


    // Validate mobile video url
    if (!empty ($_POST['mobile_url']) && !ctype_space ($_POST['mobile_url'])) {
        $data['mobile_url'] = htmlspecialchars (trim ($_POST['mobile_url']));
    } else {
        $errors['mobile_url'] = 'Invalid mobile video url';
    }


    // Validate video thumbnail url
    if (!empty ($_POST['thumb_url']) && !ctype_space ($_POST['thumb_url'])) {
        $data['thumb_url'] = htmlspecialchars (trim ($_POST['thumb_url']));
    } else {
        $errors['thumb_url'] = 'Invalid video thumbnail url';
    }


    // Validate h.264 encoding options
    if (!empty ($_POST['h264_options']) && !ctype_space ($_POST['h264_options'])) {
        $data['h264_options'] = htmlspecialchars (trim ($_POST['h264_options']));
    } else {
        $errors['h264_options'] = 'Invalid h.264 encoding options';
    }


    // Validate theora encoding options
    if (!empty ($_POST['theora_options']) && !ctype_space ($_POST['theora_options'])) {
        $data['theora_options'] = htmlspecialchars (trim ($_POST['theora_options']));
    } else {
        $errors['theora_options'] = 'Invalid theora encoding options';
    }


    // Validate mobile encoding options
    if (!empty ($_POST['mobile_options']) && !ctype_space ($_POST['mobile_options'])) {
        $data['mobile_options'] = htmlspecialchars (trim ($_POST['mobile_options']));
    } else {
        $errors['mobile_options'] = 'Invalid mobile encoding options';
    }


    // Validate thumbnail encoding options
    if (!empty ($_POST['thumb_options']) && !ctype_space ($_POST['thumb_options'])) {
        $data['thumb_options'] = htmlspecialchars (trim ($_POST['thumb_options']));
    } else {
        $errors['thumb_options'] = 'Invalid thumbnail encoding options';
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

        <form action="<?=ADMIN?>/settings_video.php" method="post">

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
                <input class="text" type="text" name="accepted_video_formats" value="<?=$data['accepted_video_formats_string']?>" />
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