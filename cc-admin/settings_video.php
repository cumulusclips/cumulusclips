<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is ltheoraed in
$userService = new UserService();
$adminUser = $userService->loginCheck();
Functions::RedirectIf($adminUser, HOST . '/login/');
Functions::RedirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$page_title = 'Video Settings';
$data = array();
$errors = array();
$warnings = array();
$message = null;

$data['php'] = Settings::Get('php');
$data['ffmpeg'] = Settings::Get('ffmpeg');
$data['h264EncodingOptions'] = Settings::Get('h264EncodingOptions');
$data['webm_encoding_options'] = Settings::get('webm_encoding_options');
$webmEncodingOptions = json_decode($data['webm_encoding_options']);
$data['theora_encoding_options'] = Settings::get('theora_encoding_options');
$theoraEncodingOptions = json_decode($data['theora_encoding_options']);
$data['mobile_encoding_options'] = Settings::get('mobile_encoding_options');
$mobileEncodingOptions = json_decode($data['mobile_encoding_options']);
$data['thumb_options'] = Settings::Get('thumb_options');
$data['debug_conversion'] = Settings::Get('debug_conversion');
$data['video_size_limit'] = Settings::Get('video_size_limit');
$data['keep_original_video'] = Settings::Get('keepOriginalVideo');





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

    // Validate video size limit
    if (isset($_POST['keep_original_video']) && in_array ($_POST['keep_original_video'], array ('1', '0'))) {
        $data['keep_original_video'] = trim($_POST['keep_original_video']);
    } else {
        $errors['keep_original_video'] = 'Invalid keep original video option';
    }

    // Validate H.264 encoding options
    if (!empty($_POST['h264EncodingOptions']) && !ctype_space($_POST['h264EncodingOptions'])) {
        $data['h264EncodingOptions'] = trim($_POST['h264EncodingOptions']);
    } else {
        $errors['h264EncodingOptions'] = 'Invalid H.264 encoding options';
    }

    // Validate WebM encoding enabled
    if (isset($_POST['webm_encoding_enabled']) && in_array($_POST['webm_encoding_enabled'], array('1', '0'))) {
        $webmEncodingOptions->enabled = $_POST['webm_encoding_enabled'] == '1' ? true : false;
        
        // Validate WebM encoding options
        if ($webmEncodingOptions->enabled) {
            if (!empty($_POST['webm_encoding_options']) && !ctype_space($_POST['webm_encoding_options'])) {
                $webmEncodingOptions->options = trim($_POST['webm_encoding_options']);
            } else {
                $errors['webm_encoding_options'] = 'Invalid WebM encoding options';
            }
        }
        $data['webm_encoding_options'] = json_encode($webmEncodingOptions);
    } else {
        $errors['webm_encoding_enabled'] = 'Invalid WebM encoding options';
    }

    // Validate Theora encoding enabled
    if (isset($_POST['theora_encoding_enabled']) && in_array($_POST['theora_encoding_enabled'], array('1', '0'))) {
        $theoraEncodingOptions->enabled = $_POST['theora_encoding_enabled'] == '1' ? true : false;
        
        // Validate Theora  encoding options
        if ($theoraEncodingOptions->enabled) {
            if (!empty($_POST['theora_encoding_options']) && !ctype_space($_POST['theora_encoding_options'])) {
                $theoraEncodingOptions->options = trim($_POST['theora_encoding_options']);
            } else {
                $errors['theora_encoding_options'] = 'Invalid Theora encoding options';
            }
        }
        $data['theora_encoding_options'] = json_encode($theoraEncodingOptions);
    } else {
        $errors['theora_encoding_enabled'] = 'Invalid Theora encoding options';
    }

    // Validate Mobile encoding enabled
    if (isset($_POST['mobile_encoding_enabled']) && in_array($_POST['mobile_encoding_enabled'], array('1', '0'))) {
        $mobileEncodingOptions->enabled = $_POST['mobile_encoding_enabled'] == '1' ? true : false;
        
        // Validate Mobile  encoding options
        if ($mobileEncodingOptions->enabled) {
            if (!empty($_POST['mobile_encoding_options']) && !ctype_space($_POST['mobile_encoding_options'])) {
                $mobileEncodingOptions->options = trim($_POST['mobile_encoding_options']);
            } else {
                $errors['mobile_encoding_options'] = 'Invalid Mobile encoding options';
            }
        }
        $data['mobile_encoding_options'] = json_encode($mobileEncodingOptions);
    } else {
        $errors['mobile_encoding_enabled'] = 'Invalid Mobile encoding options';
    }

    // Validate thumbnail encoding options
    if (!empty($_POST['thumb_options']) && !ctype_space($_POST['thumb_options'])) {
        $data['thumb_options'] = trim($_POST['thumb_options']);
    } else {
        $errors['thumb_options'] = 'Invalid thumbnail encoding options';
    }

    // Validate php-cli path
    if (empty ($_POST['php'])) {
        @exec('whereis php', $whereis_results);
        $phpPaths = explode (' ', preg_replace ('/^php:\s?/','', $whereis_results[0]));
    } else if (!empty ($_POST['php']) && file_exists ($_POST['php'])) {
        $phpPaths = array(rtrim ($_POST['php'], '/'));
    } else {
        $phpPaths = array();
    }
    
    $phpBinary = false;
    foreach ($phpPaths as $phpExe) {
        if (!is_executable($phpExe)) continue;
        @exec($phpExe . ' -r "' . "echo 'cliBinary';" . '" 2>&1 | grep cliBinary', $phpCliResults);
        $phpCliResults = implode(' ', $phpCliResults);
        if (!empty($phpCliResults)) {
            $phpCliBinary = $phpExe;
            break;
        }
    }

    if ($phpCliBinary) {
        $data['php'] = $phpCliBinary;
    } else {
        $warnings['php'] = 'Unable to locate path to PHP-CLI';
        $data['php'] = '';
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
                $data['ffmpeg'] = $whereis_results_ffmpeg;
            }

        } else {
            $data['ffmpeg'] = $which_results_ffmpeg[0];
        }

    } else if (file_exists ($_POST['ffmpeg'])) {
        $data['ffmpeg'] = rtrim ($_POST['ffmpeg'], '/');
    } else {
        $errors['ffmpeg'] = 'Invalid path to FFMPEG';
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
        $message_type = 'errors';
    }
}

// Output Header
include('header.php');

?>

<div id="settings-video">

    <h1>Video Settings</h1>

    <?php if ($message): ?>
    <div class="message <?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <div class="block">

        <form action="<?=ADMIN?>/settings_video.php" method="post">

            <div class="row <?=(isset ($errors['enable_uploads'])) ? ' error' : ''?>">
                <label>Video Uploads:</label>
                <span id="enable_uploads"><?=(Settings::Get('enable_uploads')=='1')?'Enabled':'Disabled'?></span>
            </div>

            <div class="row <?=(isset ($errors['debug_conversion'])) ? ' error' : ''?>">
                <label>Log Encoding:</label>
                <select name="debug_conversion" class="dropdown">
                    <option value="1" <?=($data['debug_conversion']=='1')?'selected="selected"':''?>>On</option>
                    <option value="0" <?=($data['debug_conversion']=='0')?'selected="selected"':''?>>Off</option>
                </select>
            </div>

            <div class="row <?=(isset ($errors['php'])) ? ' error' : ''?>">
                <label>PHP Path:</label>
                <input class="text" type="text" name="php" value="<?=$data['php']?>" />
                <a class="more-info" title="If left blank, CumulusClips will attempt to detect its location">More Info</a>
            </div>

            <div class="row <?=(isset ($errors['ffmpeg'])) ? ' error' : ''?>">
                <label>FFMPEG Path:</label>
                <input class="text" type="text" name="ffmpeg" value="<?=$data['ffmpeg']?>" />
                <a class="more-info" title="If left blank, CumulusClips will attempt to detect its location">More Info</a>
            </div>

            <div class="row <?=(isset($errors['h264EncodingOptions'])) ? ' error' : ''?>">
                <label>H.264 Encoding Options:</label>
                <input class="text" type="text" name="h264EncodingOptions" value="<?=htmlspecialchars($data['h264EncodingOptions'])?>" />
            </div>

            <div class="row <?=(isset($errors['webm_encoding_enabled'])) ? ' error' : ''?>">
                <label>WebM Encoding:</label>
                <select data-toggle="webm-encoding-options" name="webm_encoding_enabled" class="dropdown">
                    <option value="1" <?=($webmEncodingOptions->enabled == true)?'selected="selected"':''?>>Enabled</option>
                    <option value="0" <?=($webmEncodingOptions->enabled == false)?'selected="selected"':''?>>Disabled</option>
                </select>
            </div> 
            
            <div id="webm-encoding-options" class="row <?=(isset($errors['webm_encoding_options'])) ? ' error' : ''?> <?=($webmEncodingOptions->enabled == false) ? 'hide' : ''?>">
                <label>WebM Encoding Options:</label>
                <input class="text" type="text" name="webm_encoding_options" value="<?=htmlspecialchars($webmEncodingOptions->options)?>" />
            </div>
            
            <div class="row <?=(isset($errors['theora_encoding_enabled'])) ? ' error' : ''?>">
                <label>Theora Encoding:</label>
                <select data-toggle="theora-encoding-options" name="theora_encoding_enabled" class="dropdown">
                    <option value="1" <?=($theoraEncodingOptions->enabled == true)?'selected="selected"':''?>>Enabled</option>
                    <option value="0" <?=($theoraEncodingOptions->enabled == false)?'selected="selected"':''?>>Disabled</option>
                </select>
            </div> 

            <div id="theora-encoding-options" class="row <?=(isset($errors['theora_encoding_options'])) ? ' error' : ''?> <?=($theoraEncodingOptions->enabled == false) ? 'hide' : ''?>">
                <label>Theora Encoding Options:</label>
                <input class="text" type="text" name="theora_encoding_options" value="<?=htmlspecialchars($theoraEncodingOptions->options)?>" />
            </div>
            
            <div class="row <?=(isset($errors['mobile_encoding_enabled'])) ? ' error' : ''?>">
                <label>Mobile Encoding:</label>
                <select data-toggle="mobile-encoding-options" name="mobile_encoding_enabled" class="dropdown">
                    <option value="1" <?=($mobileEncodingOptions->enabled == true)?'selected="selected"':''?>>Enabled</option>
                    <option value="0" <?=($mobileEncodingOptions->enabled == false)?'selected="selected"':''?>>Disabled</option>
                </select>
            </div> 

            <div id="mobile-encoding-options" class="row <?=(isset($errors['mobile_encoding_options'])) ? ' error' : ''?> <?=($mobileEncodingOptions->enabled == false) ? 'hide' : ''?>">
                <label>Mobile Encoding Options:</label>
                <input class="text" type="text" name="mobile_encoding_options" value="<?=htmlspecialchars($mobileEncodingOptions->options)?>" />
            </div>

            <div class="row <?=(isset ($errors['thumb_options'])) ? ' error' : ''?>">
                <label>Thumbnail Options:</label>
                <input class="text" type="text" name="thumb_options" value="<?=htmlspecialchars($data['thumb_options'])?>" />
            </div>

            <div class="row <?=(isset ($errors['video_size_limit'])) ? ' error' : ''?>">
                <label>Video Site Limit:</label>
                <input class="text" type="text" name="video_size_limit" value="<?=$data['video_size_limit']?>" />
                (Bytes)
            </div>
            
            <div class="row <?=(isset($errors['keep_original_video'])) ? ' error' : ''?>">
                <label>Keep Original Video:</label>
                <select name="keep_original_video" class="dropdown">
                    <option value="1" <?=($data['keep_original_video'] == '1')?'selected="selected"':''?>>Keep</option>
                    <option value="0" <?=($data['keep_original_video'] == '0')?'selected="selected"':''?>>Discard</option>
                </select>
            </div> 

            <div class="row-shift">
                <input type="hidden" name="submitted" value="TRUE" />
                <input type="submit" class="button" value="Update Settings" />
            </div>
        </form>

    </div>


</div>

<?php include ('footer.php'); ?>