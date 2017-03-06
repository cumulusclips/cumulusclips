<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$authService->enforceTimeout(true);

// Verify user can access admin panel
$userService = new \UserService();
Functions::RedirectIf($userService->checkPermissions('manage_settings', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
clearstatcache();
$page_title = 'Video Settings';
$data = array();
$errors = array();
$warnings = array();
$message = null;

$data['php'] = Settings::get('php');
$data['ffmpeg'] = Settings::get('ffmpeg');
$data['qtfaststart'] = Settings::get('qtfaststart');
$data['thumb_encoding_options'] = Settings::get('thumb_encoding_options');
$data['debug_conversion'] = Settings::get('debug_conversion');
$data['video_size_limit'] = Settings::get('video_size_limit');
$data['keep_original_video'] = Settings::get('keep_original_video');
$data['h264_encoding_options'] = Settings::get('h264_encoding_options');
$data['webm_encoding_enabled'] = Settings::get('webm_encoding_enabled');
$data['webm_encoding_options'] = Settings::get('webm_encoding_options');
$data['theora_encoding_enabled'] = Settings::get('theora_encoding_enabled');
$data['theora_encoding_options'] = Settings::get('theora_encoding_options');
$data['mobile_encoding_enabled'] = Settings::get('mobile_encoding_enabled');
$data['mobile_encoding_options'] = Settings::get('mobile_encoding_options');

// Handle form if submitted
if (isset ($_POST['submitted'])) {

    // Validate form nonce token and submission speed
    if (
        !empty($_POST['nonce'])
        && !empty($_SESSION['formNonce'])
        && !empty($_SESSION['formTime'])
        && $_POST['nonce'] == $_SESSION['formNonce']
        && time() - $_SESSION['formTime'] >= 2
    ) {
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
        if (!empty($_POST['h264_encoding_options']) && !ctype_space($_POST['h264_encoding_options'])) {
            $data['h264_encoding_options'] = trim($_POST['h264_encoding_options']);
        } else {
            $errors['h264_encoding_options'] = 'Invalid H.264 encoding options';
        }

        // Validate Webm encoding enabled
        if (isset($_POST['webm_encoding_enabled']) && in_array($_POST['webm_encoding_enabled'], array('1', '0'))) {
            $data['webm_encoding_enabled'] = $_POST['webm_encoding_enabled'];
            $webmEncodingEnabled = $_POST['webm_encoding_enabled'] == '1' ? true : false;

            // Validate WebM encoding options
            if ($webmEncodingEnabled) {
                if (!empty($_POST['webm_encoding_options'])) {
                    $data['webm_encoding_options'] = trim($_POST['webm_encoding_options']);
                } else {
                    $errors['webm_encoding_options'] = 'Invalid WebM encoding options';
                }
            }
        } else {
            $errors['webm_encoding_enabled'] = 'Invalid value for WebM encoding enabled';
        }

        // Validate Theora encoding enabled
        if (isset($_POST['theora_encoding_enabled']) && in_array($_POST['theora_encoding_enabled'], array('1', '0'))) {
            $data['theora_encoding_enabled'] = $_POST['theora_encoding_enabled'];
            $theoraEncodingEnabled = $_POST['theora_encoding_enabled'] == '1' ? true : false;

            // Validate Theora encoding options
            if ($theoraEncodingEnabled) {
                if (!empty($_POST['theora_encoding_options'])) {
                    $data['theora_encoding_options'] = trim($_POST['theora_encoding_options']);
                } else {
                    $errors['theora_encoding_options'] = 'Invalid Theora encoding options';
                }
            }
        } else {
            $errors['theora_encoding_enabled'] = 'Invalid value for Theora encoding enabled';
        }

        // Validate Mobile encoding enabled
        if (isset($_POST['mobile_encoding_enabled']) && in_array($_POST['mobile_encoding_enabled'], array('1', '0'))) {
            $data['mobile_encoding_enabled'] = $_POST['mobile_encoding_enabled'];
            $mobileEncodingEnabled = $_POST['mobile_encoding_enabled'] == '1' ? true : false;

            // Validate Mobile encoding options
            if ($mobileEncodingEnabled) {
                if (!empty($_POST['mobile_encoding_options'])) {
                    $data['mobile_encoding_options'] = trim($_POST['mobile_encoding_options']);
                } else {
                    $errors['mobile_encoding_options'] = 'Invalid Mobile encoding options';
                }
            }
        } else {
            $errors['mobile_encoding_enabled'] = 'Invalid value for Mobile encoding enabled';
        }

        // Validate thumbnail encoding options
        if (!empty($_POST['thumb_encoding_options'])) {
            $data['thumb_encoding_options'] = trim($_POST['thumb_encoding_options']);
        } else {
            $errors['thumb_encoding_options'] = 'Invalid thumbnail encoding options';
        }

        // Validate php-cli path
        if (empty ($_POST['php'])) {
            exec('whereis php', $whereis_results);
            $phpPaths = explode (' ', preg_replace ('/^php:\s?/','', $whereis_results[0]));
        } else if (!empty ($_POST['php']) && file_exists ($_POST['php'])) {
            $phpPaths = array(rtrim ($_POST['php'], '/'));
        } else {
            $phpPaths = array();
        }

        $phpBinary = false;
        foreach ($phpPaths as $phpExe) {
            exec($phpExe . ' --version 2>&1 | grep "(cli)"', $phpCliResults);
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
        if (empty($_POST['ffmpeg'])) {

            // Determine FFMPEG path according to proccessor architecture
            $systemInfo = posix_uname();
            if (strpos($systemInfo['machine'], '64') !== false) {
                $data['ffmpeg'] = DOC_ROOT . '/cc-core/system/bin/ffmpeg-64-bit/ffmpeg';
            } else {
                $data['ffmpeg'] = DOC_ROOT . '/cc-core/system/bin/ffmpeg-32-bit/ffmpeg';
            }

        } else if (file_exists($_POST['ffmpeg'])) {
            $data['ffmpeg'] = rtrim($_POST['ffmpeg'], '/');
        } else {
            $errors['ffmpeg'] = 'Invalid path to FFMPEG';
        }

        // Validate qt-faststart path
        if (empty($_POST['qtfaststart'])) {

            // Determine qt-faststart path according to proccessor architecture
            $systemInfo = posix_uname();
            if (strpos($systemInfo['machine'], '64') !== false) {
                $data['qtfaststart'] = DOC_ROOT . '/cc-core/system/bin/ffmpeg-64-bit/qt-faststart';
            } else {
                $data['qtfaststart'] = DOC_ROOT . '/cc-core/system/bin/ffmpeg-32-bit/qt-faststart';
            }

        } else if (file_exists($_POST['qtfaststart'])) {
            $data['qtfaststart'] = rtrim($_POST['qtfaststart'], '/');
        } else {
            $errors['qtfaststart'] = 'Invalid path to qt-faststart';
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
                $message_type = 'alert-warning';

            } else {
                $data['enable_uploads'] = 1;
                $message = 'Settings have been updated';
                $message .= (Settings::Get ('enable_uploads') == 0) ? ', and video uploads have been enabled.' : '.';
                $message_type = 'alert-success';
            }


            foreach ($data as $key => $value) {
                Settings::Set ($key, $value);
            }

        } else {
            $message = 'The following errors were found. Please correct them and try again.';
            $message .= '<br /><br /> - ' . implode ('<br /> - ', $errors);
            $message_type = 'alert-danger';
        }

    } else {
        $message = 'Expired or invalid session';
        $message_type = 'alert-danger';
    }
}

// Generate new form nonce
$formNonce = md5(uniqid(rand(), true));
$_SESSION['formNonce'] = $formNonce;
$_SESSION['formTime'] = time();

// Output Header
$pageName = 'settings-videos';
include('header.php');

?>

<h1>Video Settings</h1>

<?php if ($message): ?>
<div class="alert <?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<form action="<?=ADMIN?>/settings_video.php" method="post">

    <div class="form-group <?=(isset ($errors['enable_uploads'])) ? 'has-error' : ''?>">
        <label class="control-label">Video Uploads:</label>
        <span id="enable_uploads"><?=(Settings::Get('enable_uploads')=='1')?'Enabled':'Disabled'?></span>
    </div>

    <div class="form-group <?=(isset ($errors['debug_conversion'])) ? 'has-error' : ''?>">
        <label class="control-label">Log Encoding:</label>
        <select name="debug_conversion" class="form-control">
            <option value="1" <?=($data['debug_conversion']=='1')?'selected="selected"':''?>>On</option>
            <option value="0" <?=($data['debug_conversion']=='0')?'selected="selected"':''?>>Off</option>
        </select>
    </div>

    <div class="form-group <?=(isset ($errors['php'])) ? 'has-error' : ''?>">
        <label class="control-label">PHP Path:</label>
        <input class="form-control" type="text" name="php" value="<?=$data['php']?>" />
        <a class="more-info" title="If left blank, CumulusClips will attempt to detect its location">More Info</a>
    </div>

    <div class="form-group <?=(isset ($errors['ffmpeg'])) ? 'has-error' : ''?>">
        <label class="control-label">FFMPEG Path:</label>
        <input class="form-control" type="text" name="ffmpeg" value="<?=$data['ffmpeg']?>" />
        <a class="more-info" title="If left blank, CumulusClips will use it's built in FFMPEG binary">More Info</a>
    </div>

    <div class="form-group <?=(isset ($errors['qtfaststart'])) ? 'has-error' : ''?>">
        <label class="control-label">qt-faststart Path:</label>
        <input class="form-control" type="text" name="qtfaststart" value="<?=$data['qtfaststart']?>" />
        <a class="more-info" title="If left blank, CumulusClips will use it's built-in qt-faststart">More Info</a>
    </div>

    <div class="form-group <?=(isset($errors['h264_encoding_options'])) ? 'has-error' : ''?>">
        <label class="control-label">H.264 Encoding Options:</label>
        <input class="form-control" type="text" name="h264_encoding_options" value="<?=htmlspecialchars($data['h264_encoding_options'])?>" />
    </div>

    <div class="form-group <?=(isset($errors['webm_encoding_enabled'])) ? 'has-error' : ''?>">
        <label class="control-label">WebM Encoding:</label>
        <select data-toggle="webm-encoding-options" name="webm_encoding_enabled" class="form-control">
            <option value="1" <?=($data['webm_encoding_enabled'] == '1')?'selected="selected"':''?>>Enabled</option>
            <option value="0" <?=($data['webm_encoding_enabled'] == '0')?'selected="selected"':''?>>Disabled</option>
        </select>
    </div>

    <div id="webm-encoding-options" class="form-group <?=(isset($errors['webm_encoding_options'])) ? 'has-error' : ''?> <?=($data['webm_encoding_enabled'] == '0') ? 'hide' : ''?>">
        <label class="control-label">WebM Encoding Options:</label>
        <input class="form-control" type="text" name="webm_encoding_options" value="<?=htmlspecialchars($data['webm_encoding_options'])?>" />
    </div>

    <div class="form-group <?=(isset($errors['theora_encoding_enabled'])) ? 'has-error' : ''?>">
        <label class="control-label">Theora Encoding:</label>
        <select data-toggle="theora-encoding-options" name="theora_encoding_enabled" class="form-control">
            <option value="1" <?=($data['theora_encoding_enabled'] == '1')?'selected="selected"':''?>>Enabled</option>
            <option value="0" <?=($data['theora_encoding_enabled'] == '0')?'selected="selected"':''?>>Disabled</option>
        </select>
    </div>

    <div id="theora-encoding-options" class="form-group <?=(isset($errors['theora_encoding_options'])) ? 'has-error' : ''?> <?=($data['theora_encoding_enabled'] == '0') ? 'hide' : ''?>">
        <label class="control-label">Theora Encoding Options:</label>
        <input class="form-control" type="text" name="theora_encoding_options" value="<?=htmlspecialchars($data['theora_encoding_options'])?>" />
    </div>

    <div class="form-group <?=(isset($errors['mobile_encoding_enabled'])) ? 'has-error' : ''?>">
        <label class="control-label">Mobile Encoding:</label>
        <select data-toggle="mobile-encoding-options" name="mobile_encoding_enabled" class="form-control">
            <option value="1" <?=($data['mobile_encoding_enabled'] == '1')?'selected="selected"':''?>>Enabled</option>
            <option value="0" <?=($data['mobile_encoding_enabled'] == '0')?'selected="selected"':''?>>Disabled</option>
        </select>
    </div>

    <div id="mobile-encoding-options" class="form-group <?=(isset($errors['mobile_encoding_options'])) ? 'has-error' : ''?> <?=($data['mobile_encoding_enabled'] == '0') ? 'hide' : ''?>">
        <label class="control-label">Mobile Encoding Options:</label>
        <input class="form-control" type="text" name="mobile_encoding_options" value="<?=htmlspecialchars($data['mobile_encoding_options'])?>" />
    </div>

    <div class="form-group <?=(isset ($errors['thumb_encoding_options'])) ? 'has-error' : ''?>">
        <label class="control-label">Thumbnail Options:</label>
        <input class="form-control" type="text" name="thumb_encoding_options" value="<?=htmlspecialchars($data['thumb_encoding_options'])?>" />
    </div>

    <div class="form-group <?=(isset ($errors['video_size_limit'])) ? 'has-error' : ''?>">
        <label class="control-label">Video Size Limit:</label>
        <input class="form-control" type="text" name="video_size_limit" value="<?=$data['video_size_limit']?>" />
        (Bytes)
    </div>

    <div class="form-group <?=(isset($errors['keep_original_video'])) ? 'has-error' : ''?>">
        <label class="control-label">Keep Original Video:</label>
        <select name="keep_original_video" class="form-control">
            <option value="1" <?=($data['keep_original_video'] == '1')?'selected="selected"':''?>>Keep</option>
            <option value="0" <?=($data['keep_original_video'] == '0')?'selected="selected"':''?>>Discard</option>
        </select>
    </div>

    <input type="hidden" name="submitted" value="TRUE" />
    <input type="hidden" name="nonce" value="<?=$formNonce?>" />
    <input type="submit" class="button" value="Update Settings" />

</form>

<?php include ('footer.php'); ?>