<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$userService = new UserService();
$adminUser = $userService->loginCheck();
Functions::RedirectIf($adminUser, HOST . '/login/');
Functions::RedirectIf($userService->checkPermissions('manage_settings', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$page_title = 'General Settings';
$data = array();
$errors = array();
$message = null;

// Retrieve settings from database
$data['sitename'] = Settings::get('sitename');
$data['base_url'] = Settings::get('base_url');
$data['admin_email'] = Settings::get('admin_email');
$data['user_uploads'] = Settings::get('user_uploads');
$data['auto_approve_videos'] = Settings::get('auto_approve_videos');
$data['user_registrations'] = Settings::get('user_registrations');
$data['auto_approve_users'] = Settings::get('auto_approve_users');
$data['auto_approve_comments'] = Settings::get('auto_approve_comments');
$data['mobile_site'] = Settings::get('mobile_site');

// Handle form if submitted
if (isset($_POST['submitted'])) {

    // Validate sitename
    if (!empty($_POST['sitename']) && !ctype_space($_POST['sitename'])) {
        $data['sitename'] = trim($_POST['sitename']);
    } else {
        $errors['sitename'] = 'Invalid sitename';
    }

    // Validate base_url
    $pattern = '/^https?:\/\/[a-z0-9][a-z0-9\.\-]+.*$/i';
    if (!empty($_POST['base_url']) && preg_match($pattern, $_POST['base_url'])) {
        $data['base_url'] = rtrim($_POST['base_url'], '/');
    } else {
        $errors['base_url'] = 'Invalid base url';
    }

    // Validate admin_email
    $pattern = '/^[a-z0-9][a-z0-9\.\-]+@[a-z0-9][a-z0-9\.\-]+$/i';
    if (!empty($_POST['admin_email']) && preg_match($pattern, $_POST['admin_email'])) {
        $data['admin_email'] = trim($_POST['admin_email']);
    } else {
        $errors['admin_email'] = 'Invalid admin email';
    }

    // Validate user_uploads
    if (isset($_POST['user_uploads']) && in_array($_POST['user_uploads'], array('1', '0'))) {
        $data['user_uploads'] = $_POST['user_uploads'];
        if ($data['user_uploads'] == '0') {
            $data['auto_approve_videos'] = '1';
        }
    } else {
        $errors['user_uploads'] = 'Invalid user upload option';
    }

    // Validate auto_approve_videos
    if ($data['user_uploads'] == '1') {
        if (isset($_POST['auto_approve_videos']) && in_array($_POST['auto_approve_videos'], array('1', '0'))) {
            $data['auto_approve_videos'] = $_POST['auto_approve_videos'];
        } else {
            $errors['auto_approve_videos'] = 'Invalid video approval option';
        }
    }

    // Validate allow user registrations
    if (isset($_POST['user_registrations']) && in_array($_POST['user_registrations'], array('1', '0'))) {
        $data['user_registrations'] = $_POST['user_registrations'];
        if ($data['user_registrations'] == '0') {
            $data['auto_approve_users'] = '1';
        }
    } else {
        $errors['user_registrations'] = 'Invalid user registration option';
    }

    // Validate auto_approve_users
    if ($data['user_registrations'] == '1') {
        if (isset($_POST['auto_approve_users']) && in_array($_POST['auto_approve_users'], array ('1', '0'))) {
            $data['auto_approve_users'] = $_POST['auto_approve_users'];
        } else {
            $errors['auto_approve_users'] = 'Invalid member approval option';
        }
        $data['auto_approve_users'] = '1';
    }

    // Validate auto_approve_comments
    if (isset($_POST['auto_approve_comments']) && in_array($_POST['auto_approve_comments'], array('1', '0'))) {
        $data['auto_approve_comments'] = $_POST['auto_approve_comments'];
    } else {
        $errors['auto_approve_comments'] = 'Invalid comment approval option';
    }

    // Validate mobile site
    if (isset($_POST['mobile_site']) && in_array($_POST['mobile_site'], array('1', '0'))) {
        $data['mobile_site'] = $_POST['mobile_site'];
    } else {
        $errors['mobile_site'] = 'Invalid mobile site value';
    }

    // Update video if no errors were made
    if (empty($errors)) {
        foreach ($data as $key => $value) {
            Settings::set($key, $value);
        }
        $message = 'Settings have been updated.';
        $message_type = 'alert-success';
    } else {
        $message = 'The following errors were found. Please correct them and try again.';
        $message .= '<br /><br /> - ' . implode('<br /> - ', $errors);
        $message_type = 'alert-danger';
    }
}


// Output Header
$pageName = 'settings';
include ('header.php');

?>

<h1>General Settings</h1>

<?php if ($message): ?>
<div class="alert <?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<form action="<?=ADMIN?>/settings.php" method="post">

    <div class="form-group <?=(isset ($errors['sitename'])) ? 'has-error' : ''?>">
        <label class="control-label">Sitename:</label>
        <input class="form-control" type="text" name="sitename" value="<?=$data['sitename']?>" />
    </div>

    <div class="form-group <?=(isset ($errors['base_url'])) ? 'has-error' : ''?>">
        <label class="control-label">Base URL:</label>
        <input class="form-control" type="text" name="base_url" value="<?=$data['base_url']?>" />
    </div>

    <div class="form-group <?=(isset ($errors['admin_email'])) ? 'has-error' : ''?>">
        <label class="control-label">Admin Email:</label>
        <input class="form-control" type="text" name="admin_email" value="<?=$data['admin_email']?>" />
    </div>

    <div class="form-group <?=(isset ($errors['user_uploads'])) ? 'has-error' : ''?>">
        <label class="control-label">Member Video Uploads:</label>
        <select name="user_uploads" class="form-control" data-toggle="user-video-approval">
            <option value="1" <?=($data['user_uploads']=='1')?'selected="selected"':''?>>Enabled</option>
            <option value="0" <?=($data['user_uploads']=='0')?'selected="selected"':''?>>Disabled</option>
        </select>
        <a class="more-info" title="Whether to allow standard users to upload videos. If disabled, only Admins and Moderators are allowed to upload videos">More Info</a>
    </div>

    <div id="user-video-approval" class="form-group <?=($data['user_uploads'] == '1') ? '' : 'hide'?> <?=(isset ($errors['auto_approve_videos'])) ? 'has-error' : ''?>">
        <label class="control-label">Video Approval:</label>
        <select name="auto_approve_videos" class="form-control">
            <option value="1" <?=($data['auto_approve_videos']=='1')?'selected="selected"':''?>>Auto-Approve</option>
            <option value="0" <?=($data['auto_approve_videos']=='0')?'selected="selected"':''?>>Approval Required</option>
        </select>
    </div>

    <div class="form-group <?=(isset ($errors['user_registrations'])) ? 'has-error' : ''?>">
        <label class="control-label">Member Registrations:</label>
        <select name="user_registrations" class="form-control" data-toggle="user-registration-approval">
            <option value="1" <?=($data['user_registrations']=='1')?'selected="selected"':''?>>Enabled</option>
            <option value="0" <?=($data['user_registrations']=='0')?'selected="selected"':''?>>Disabled</option>
        </select>
    </div>

    <div id="user-registration-approval" class="form-group <?=($data['user_registrations'] == '1') ? '' : 'hide'?> <?=(isset($errors['auto_approve_users'])) ? 'has-error' : ''?>">
        <label class="control-label">Member Approval:</label>
        <select name="auto_approve_users" class="form-control">
            <option value="1" <?=($data['auto_approve_users']=='1')?'selected="selected"':''?>>Auto-Approve</option>
            <option value="0" <?=($data['auto_approve_users']=='0')?'selected="selected"':''?>>Approval Required</option>
        </select>
    </div>

    <div class="form-group <?=(isset ($errors['auto_approve_comments'])) ? 'has-error' : ''?>">
        <label class="control-label">Comment Approval:</label>
        <select name="auto_approve_comments" class="form-control">
            <option value="1" <?=($data['auto_approve_comments']=='1')?'selected="selected"':''?>>Auto-Approve</option>
            <option value="0" <?=($data['auto_approve_comments']=='0')?'selected="selected"':''?>>Approval Required</option>
        </select>
    </div>

    <div class="form-group <?=(isset ($errors['mobile_site'])) ? 'has-error' : ''?>">
        <label class="control-label">Mobile Site:</label>
        <select name="mobile_site" class="form-control">
            <option value="1" <?=($data['mobile_site']=='1')?'selected="selected"':''?>>Enabled</option>
            <option value="0" <?=($data['mobile_site']=='0')?'selected="selected"':''?>>Disabled</option>
        </select>
    </div>

    <input type="hidden" name="submitted" value="TRUE" />
    <input type="submit" class="button" value="Update Settings" />
        
</form>

<?php include ('footer.php'); ?>