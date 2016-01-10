<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$userService = new UserService();
$adminUser = $userService->loginCheck();
Functions::RedirectIf($adminUser, HOST . '/login/');
Functions::RedirectIf($userService->checkPermissions('manage_settings', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$page_title = 'Email Settings';
$data = array();
$errors = array();
$message = null;
$data['alerts_videos'] = Settings::get('alerts_videos');
$data['alerts_comments'] = Settings::get('alerts_comments');
$data['alerts_users'] = Settings::get('alerts_users');
$data['alerts_flags'] = Settings::get('alerts_flags');
$data['from_name'] = Settings::get('from_name');
$data['from_address'] = Settings::get('from_address');
$data['smtp'] = json_decode(Settings::get('smtp'));
$data['smtp_enabled'] = $data['smtp']->enabled;
$data['smtp_host'] = $data['smtp']->host;
$data['smtp_port'] = $data['smtp']->port;
$data['smtp_username'] = $data['smtp']->username;
$data['smtp_password'] = $data['smtp']->password;

// Handle form if submitted
if (isset($_POST['submitted'])) {

    // Validate video alerts
    if (isset($_POST['alerts_videos']) && in_array($_POST['alerts_videos'], array('1', '0'))) {
        $data['alerts_videos'] = $_POST['alerts_videos'];
    } else {
        $errors['alerts_videos'] = 'Invalid video alert option';
    }

    // Validate video comment alerts
    if (isset($_POST['alerts_comments']) && in_array($_POST['alerts_comments'], array('1', '0'))) {
        $data['alerts_comments'] = $_POST['alerts_comments'];
    } else {
        $errors['alerts_comments'] = 'Invalid video comments alert option';
    }

    // Validate flagged content alerts
    if (isset($_POST['alerts_flags']) && in_array($_POST['alerts_flags'], array('1', '0'))) {
        $data['alerts_flags'] = $_POST['alerts_flags'];
    } else {
        $errors['alerts_flags'] = 'Invalid content flag alert option';
    }

    // Validate user signup alerts
    if (isset($_POST['alerts_users']) && in_array($_POST['alerts_users'], array('1', '0'))) {
        $data['alerts_users'] = $_POST['alerts_users'];
    } else {
        $errors['alerts_users'] = 'Invalid member signups alert option';
    }

    // Validate from email name
    if (!empty($_POST['from_name'])) {
        if (!ctype_space($_POST['from_name'])) {
            $data['from_name'] = trim($_POST['from_name']);
        } else {
            $errors['from_name'] = 'Invalid from name';
        }
    } else {
        $data['from_name'] = '';
    }
    
    // Validate from email address
    if (!empty($_POST['from_address'])) {
        $pattern = '/^[a-z0-9][a-z0-9\.\-]+@[a-z0-9][a-z0-9\.\-]+$/i';
        if (preg_match($pattern, $_POST['from_address'])) {
            $data['from_address'] = trim($_POST['from_address']);
        } else {
            $errors['from_address'] = 'Invalid from email address';
        }
    } else {
        $data['from_address'] = '';
    }

    // Validate smtp enabled option
    if (isset($_POST['smtp_enabled']) && in_array($_POST['smtp_enabled'], array('1', '0'))) {
        $data['smtp']->enabled = ($_POST['smtp_enabled'] == '1') ? true : false;
        if (!$data['smtp']->enabled) {
            $data['smtp']->host = $data['smtp_host'] = '';
            $data['smtp']->port = $data['smtp_port'] = 25;
            $data['smtp']->password = $data['smtp_password'] = '';
            $data['smtp']->username = $data['smtp_username'] = '';
        }
    } else {
        $errors['smtp_enabled'] = 'Invalid SMTP enablement option';
    }
    
    // Validate smtp auth settings if enabled
    if ($data['smtp']->enabled) {
        
        // Validate smtp host
        $pattern = '/^[a-z0-9][a-z0-9\.\-]+$/i';
        if (!empty($_POST['smtp_host']) && preg_match($pattern, $_POST['smtp_host'])) {
            $data['smtp']->host = $data['smtp_host'] = trim($_POST['smtp_host']);
        } else {
            $errors['smtp_host'] = 'Invalid SMTP hostname';
        }

        // Validate smtp port
        if (isset($_POST['smtp_port']) && is_numeric($_POST['smtp_port'])) {
            $data['smtp']->port = $data['smtp_port'] = $_POST['smtp_port'];
        } else {
            $errors['smtp_port'] = 'Invalid SMTP port';
        }

        // Validate smtp username
        if (!empty($_POST['smtp_username']) && !ctype_space($_POST['smtp_username'])) {
            $data['smtp']->username = $data['smtp_username'] = trim($_POST['smtp_username']);
        } else {
            $errors['smtp_username'] = 'Invalid SMTP username';
        }

        // Validate smtp password
        if (!empty ($_POST['smtp_password']) && !ctype_space ($_POST['smtp_password'])) {
            $data['smtp']->password = $data['smtp_password'] = trim($_POST['smtp_password']);
        } else {
            $errors['smtp_password'] = 'Invalid SMTP password';
        }
    }

    // Update video if no errors were made
    if (empty($errors)) {
        $data['smtp'] = json_encode($data['smtp']);
        foreach ($data as $key => $value) {
            Settings::set($key, $value);
        }
        $data['smtp'] = json_decode($data['smtp']);
        $message = 'Settings have been updated.';
        $message_type = 'alert-success';
    } else {
        $message = 'The following errors were found. Please correct them and try again.';
        $message .= '<br /><br /> - ' . implode('<br /> - ', $errors);
        $message_type = 'alert-danger';
    }
}


// Output Header
$pageName = 'settings-email';
include('header.php');

?>

<h1>Email Settings</h1>

<?php if ($message): ?>
<div class="alert <?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<form action="<?=ADMIN?>/settings_email.php" method="post">

    <p class="h3">System Alerts</p>

    <div class="form-group <?=(isset ($errors['alerts_videos'])) ? 'has-error' : ''?>">
        <label class="control-label">New Video Alerts:</label>
        <select name="alerts_videos" class="form-control">
            <option value="1" <?=($data['alerts_videos']=='1')?'selected="selected"':''?>>Enabled</option>
            <option value="0" <?=($data['alerts_videos']=='0')?'selected="selected"':''?>>Disabled</option>
        </select>
    </div>

    <div class="form-group <?=(isset ($errors['alerts_comments'])) ? 'has-error' : ''?>">
        <label class="control-label">Video Comment Alerts:</label>
        <select name="alerts_comments" class="form-control">
            <option value="1" <?=($data['alerts_comments']=='1')?'selected="selected"':''?>>Enabled</option>
            <option value="0" <?=($data['alerts_comments']=='0')?'selected="selected"':''?>>Disabled</option>
        </select>
    </div>

    <div class="form-group <?=(isset ($errors['alerts_users'])) ? 'has-error' : ''?>">
        <label class="control-label">New Member Alerts:</label>
        <select name="alerts_users" class="form-control">
            <option value="1" <?=($data['alerts_users']=='1')?'selected="selected"':''?>>Enabled</option>
            <option value="0" <?=($data['alerts_users']=='0')?'selected="selected"':''?>>Disabled</option>
        </select>
    </div>

    <div class="form-group <?=(isset ($errors['alerts_flags'])) ? 'has-error' : ''?>">
        <label class="control-label">Flagged Content Alerts:</label>
        <select name="alerts_flags" class="form-control">
            <option value="1" <?=($data['alerts_flags']=='1')?'selected="selected"':''?>>Enabled</option>
            <option value="0" <?=($data['alerts_flags']=='0')?'selected="selected"':''?>>Disabled</option>
        </select>
    </div>



    <p class="h3">Email Configuration</p>

    <div class="form-group <?=(isset ($errors['from_name'])) ? 'has-error' : ''?>">
        <label class="control-label">"From" Name:</label>
        <input class="form-control" type="text" name="from_name" value="<?=htmlspecialchars($data['from_name'])?>" /> <a class="more-info" title="If left blank, defaults to '<?=Settings::get('sitename')?>'">More Info</a>
    </div>

    <div class="form-group <?=(isset ($errors['from_address'])) ? 'has-error' : ''?>">
        <label class="control-label">"From" Email Address:</label>
        <input class="form-control" type="text" name="from_address" value="<?=htmlspecialchars($data['from_address'])?>" /> <a class="more-info" title="If left blank, defaults to 'cumulusclips@<?=$_SERVER['SERVER_NAME']?>'">More Info</a>
    </div>

    <div class="form-group <?=(isset ($errors['smtp_enabled'])) ? 'has-error' : ''?>">
        <label class="control-label">SMTP Authentication:</label>
        <select data-toggle="smtp_auth" name="smtp_enabled" class="form-control">
            <option value="1" <?=($data['smtp']->enabled)?'selected="selected"':''?>>Enabled</option>
            <option value="0" <?=(!$data['smtp']->enabled)?'selected="selected"':''?>>Disabled</option>
        </select>
    </div>

    <!-- BEGIN SMTP AUTH SETTINGS -->
    <div id="smtp_auth" class="<?=(!$data['smtp']->enabled)?'hide':''?>">

        <div class="form-group <?=(isset ($errors['smtp_host'])) ? 'has-error' : ''?>">
            <label class="control-label">SMTP Host:</label>
            <input class="form-control" type="text" name="smtp_host" value="<?=htmlspecialchars($data['smtp_host'])?>" />
        </div>

        <div class="form-group <?=(isset ($errors['smtp_port'])) ? 'has-error' : ''?>">
            <label class="control-label">SMTP Port:</label>
            <input class="form-control" type="text" name="smtp_port" value="<?=$data['smtp_port']?>" />
        </div>

        <div class="form-group <?=(isset ($errors['smtp_username'])) ? 'has-error' : ''?>">
            <label class="control-label">SMTP Username:</label>
            <input class="form-control" type="text" name="smtp_username" value="<?=htmlspecialchars($data['smtp_username'])?>" />
        </div>

        <div class="form-group <?=(isset ($errors['smtp_password'])) ? 'has-error' : ''?>">
            <label class="control-label">SMTP Password:</label>
            <input class="form-control mask" type="password" name="smtp_password" value="<?=htmlspecialchars($data['smtp_password'])?>" />
        </div>

    </div>
    <!-- END SMTP AUTH SETTINGS -->

    <input type="hidden" name="submitted" value="TRUE" />
    <input type="submit" class="button" value="Update Settings" />

</form>

<?php include ('footer.php'); ?>