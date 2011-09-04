<?php

### Created on February 28, 2009
### Created by Miguel A. Hurtado
### This script displays the site homepage


// Include required files
include ('../cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');


// Establish page variables, objects, arrays, etc
Plugin::Trigger ('admin.video_edit.start');
Functions::RedirectIf ($logged_in = User::LoginCheck(), HOST . '/login/');
$admin = new User ($logged_in);
Functions::RedirectIf (User::CheckPermissions ('admin_panel', $admin), HOST . '/myaccount/');
$page_title = 'Video Settings';
$data = array();
$errors = array();
$warnings = array();
$message = null;

$data['php'] = Settings::Get('php');
$data['ffmpeg'] = Settings::Get('ffmpeg');
$data['qt_faststart'] = Settings::Get('qt_faststart');
$data['h264_options'] = Settings::Get('h264_options');
$data['theora_options'] = Settings::Get('theora_options');
$data['mobile_options'] = Settings::Get('mobile_options');
$data['thumb_options'] = Settings::Get('thumb_options');
$data['debug_conversion'] = Settings::Get('debug_conversion');
$data['video_size_limit'] = Settings::Get('video_size_limit');
$data['h264_url'] = Settings::Get('h264_url');
$data['theora_url'] = Settings::Get('theora_url');
$data['mobile_url'] = Settings::Get('mobile_url');
$data['thumb_url'] = Settings::Get('thumb_url');





/***********************
Handle form if submitted
***********************/

if (isset ($_POST['submitted'])) {

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


    // Validate h.264 video url
    if (!empty ($_POST['h264_url']) && !ctype_space ($_POST['h264_url'])) {
        $data['h264_url'] = htmlspecialchars (trim ($_POST['h264_url']));
    } else {
        $data['h264_url'] = '';
    }


    // Validate theora video url
    if (!empty ($_POST['theora_url']) && !ctype_space ($_POST['theora_url'])) {
        $data['theora_url'] = htmlspecialchars (trim ($_POST['theora_url']));
    } else {
        $data['theora_url'] = '';
    }


    // Validate mobile video url
    if (!empty ($_POST['mobile_url']) && !ctype_space ($_POST['mobile_url'])) {
        $data['mobile_url'] = htmlspecialchars (trim ($_POST['mobile_url']));
    } else {
        $data['mobile_url'] = '';
    }


    // Validate video thumbnail url
    if (!empty ($_POST['thumb_url']) && !ctype_space ($_POST['thumb_url'])) {
        $data['thumb_url'] = htmlspecialchars (trim ($_POST['thumb_url']));
    } else {
        $data['thumb_url'] = '';
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


    // Validate php path
    if (empty ($_POST['php'])) {

        // Find php path (using which)
        @exec ('which php', $which_results);
        if (empty ($which_results)) {

            // Find PHP path (using whereis)
            @exec ('whereis php', $whereis_results);
            $whereis_results = preg_replace ('/^php:\s?/','', $whereis_results[0]);
            if (empty ($whereis_results)) {
                $warnings['php'] = 'Unable to detect path to PHP';
                $data['php'] = '';
            } else {
                $path = explode (' ', $whereis_results[0]);
                $data['php'] = $path[0];
            }

        } else {
            $data['php'] = $which_results[0];
        }

    } else if (file_exists ($_POST['php'])) {
        $data['php'] = rtrim ($_POST['php'], '/');
    } else {
        $errors['php'] = 'Invalid path to PHP';
    }



    // Validate ffmpeg path
    if (empty ($_POST['ffmpeg'])) {

        // Check if FFMPEG is installed (using which)
        @exec ('which ffmpeg', $which_results_ffmpeg);
        if (empty ($which_results_ffmpeg)) {

            // Check if FFMPEG is installed (using whereis)
            @exec ('whereis ffmpeg', $whereis_results_ffmpeg);
            $whereis_results_ffmpeg = preg_replace ('/^ffmpeg:\s?/','', $whereis_results_ffmpeg[0]);
            if (empty ($whereis_results_ffmpeg)) {
                $warnings['ffmpeg'] = 'Unable to locate FFMPEG';
                $data['ffmpeg'] = '';
            } else {
                $path_ffmpeg = explode (' ', $whereis_results_ffmpeg[0]);
                $data['ffmpeg'] = $path_ffmpeg[0];
            }

        } else {
            $data['ffmpeg'] = $which_results_ffmpeg[0];
        }

    } else if (file_exists ($_POST['ffmpeg'])) {
        $data['ffmpeg'] = rtrim ($_POST['ffmpeg'], '/');
    } else {
        $errors['ffmpeg'] = 'Invalid path to FFMPEG';
    }


    // Validate qt-fast start path
    if (empty ($_POST['qt_faststart'])) {

        // Check if qt-faststart is installed (using which)
        @exec ('which qt-faststart', $which_results_faststart);
        if (empty ($which_results_faststart)) {

            // Check if qt-faststart is installed (using whereis)
            @exec ('whereis qt-faststart', $whereis_results_faststart);
            $whereis_results_faststart = preg_replace ('/^qt\-faststart:\s?/','', $whereis_results_faststart[0]);
            if (empty ($whereis_results_faststart)) {
                $warnings['qt_faststart'] = 'Unable to located qt-faststart';
                $data['qt_faststart'] = '';
            } else {
                $path_faststart = explode (' ', $whereis_results_faststart[0]);
                $data['qt_faststart'] = $path_faststart[0];
            }

        } else {
            $data['qt_faststart'] = $which_results_faststart[0];
        }

    } else if (file_exists ($_POST['qt_faststart'])) {
        $data['qt_faststart'] = rtrim ($_POST['qt_faststart'], '/');
    } else {
        $errors['qt_faststart'] = 'Invalid path to qt-faststart';
    }


    
    // Update video if no errors were made
    if (empty ($errors)) {

        // Check if there were warnings
        if (!empty ($warnings)) {

            $data['enable_uploads'] = 0;
            $message = 'Settings have been updated, but there are notices.';
            $message .= '<h3>Notice:</h3>';
            $message .= '<p>The following requirements were not met. As a result video uploads have been disabled.';
            $message .= '<br /><br /> - ' . implode ('<br /> - ', $warnings);
            $message .= '</p><p class="small">If you\'re using a plugin or service for encoding videos you can ignore this message.</p>';
            $message_type = 'notice';

        } else {
            $data['enable_uploads'] = 1;
            $message = 'Settings have been updated';
            $message .= (Settings::Get ('enable_uploads') == 0) ? ', and video uploads have been enabled.' : '.';
            $message_type = 'success';
        }

        foreach ($data as $key => $value) {
            Settings::Set ($key, $value);
        }

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
                <span id="enable_uploads"><?=(Settings::Get('enable_uploads')=='1')?'Enabled':'Disabled'?></span>
            </div>

            <div class="row <?=(isset ($errors['debug_conversion'])) ? ' errors' : ''?>">
                <label>Log Encoding:</label>
                <select name="debug_conversion" class="dropdown">
                    <option value="1" <?=($data['debug_conversion']=='1')?'selected="selected"':''?>>On</option>
                    <option value="0" <?=($data['debug_conversion']=='0')?'selected="selected"':''?>>Off</option>
                </select>
            </div>

            <div class="row <?=(isset ($errors['php'])) ? ' errors' : ''?>">
                <label>PHP Path:</label>
                <input class="text" type="text" name="php" value="<?=$data['php']?>" />
                <a class="more-info" title="If left blank, CumulusClips will attempt to detect its location">More Info</a>
            </div>

            <div class="row <?=(isset ($errors['ffmpeg'])) ? ' errors' : ''?>">
                <label>FFMPEG Path:</label>
                <input class="text" type="text" name="ffmpeg" value="<?=$data['ffmpeg']?>" />
                <a class="more-info" title="If left blank, CumulusClips will attempt to detect its location">More Info</a>
            </div>

            <div class="row <?=(isset ($errors['qt_faststart'])) ? ' errors' : ''?>">
                <label>qt-faststart Path:</label>
                <input class="text" type="text" name="qt_faststart" value="<?=$data['qt_faststart']?>" />
                <a class="more-info" title="If left blank, CumulusClips will attempt to detect its location">More Info</a>
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

            <div class="row <?=(isset ($errors['video_size_limit'])) ? ' errors' : ''?>">
                <label>Video Site Limit:</label>
                <input class="text" type="text" name="video_size_limit" value="<?=$data['video_size_limit']?>" />
                (Bytes)
            </div>

            <div class="row <?=(isset ($errors['h264_url'])) ? ' errors' : ''?>">
                <label>h.264 (MP4) Video URL:</label>
                <input class="text" type="text" name="h264_url" value="<?=$data['h264_url']?>" />
                <a class="more-info" title="If left blank, defaults to '<?=HOST?>/cc-content/uploads/h264/'">More Info</a>
            </div>

            <div class="row <?=(isset ($errors['theora_url'])) ? ' errors' : ''?>">
                <label>Theora (OGG) Video URL:</label>
                <input class="text" type="text" name="theora_url" value="<?=$data['theora_url']?>" />
                <a class="more-info" title="If left blank, defaults to '<?=HOST?>/cc-content/uploads/theora/'">More Info</a>
            </div>

            <div class="row <?=(isset ($errors['mobile_url'])) ? ' errors' : ''?>">
                <label>Mobile Video URL:</label>
                <input class="text" type="text" name="mobile_url" value="<?=$data['mobile_url']?>" />
                <a class="more-info" title="If left blank, defaults to '<?=HOST?>/cc-content/uploads/mobile/'">More Info</a>
            </div>

            <div class="row <?=(isset ($errors['thumb_url'])) ? ' errors' : ''?>">
                <label>Thumbnail URL:</label>
                <input class="text" type="text" name="thumb_url" value="<?=$data['thumb_url']?>" />
                <a class="more-info" title="If left blank, defaults to '<?=HOST?>/cc-content/uploads/thumbs/'">More Info</a>
            </div>

            <div class="row-shift">
                <input type="hidden" name="submitted" value="TRUE" />
                <input type="submit" class="button" value="Update Settings" />
            </div>
        </form>

    </div>


</div>

<?php include ('footer.php'); ?>