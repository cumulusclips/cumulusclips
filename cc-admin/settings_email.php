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
$page_title = 'Email Settings';
$data = array();
$errors = array();
$message = null;
$smtp_auth = null;


$data['alerts_videos'] = Settings::Get('alerts_videos');
$data['alerts_comments'] = Settings::Get('alerts_comments');
$data['alerts_ratings'] = Settings::Get('alerts_ratings');
$data['alerts_users'] = Settings::Get('alerts_users');
$data['from_name'] = Settings::Get('from_name');
$data['from_address'] = Settings::Get('from_address');
$data['smtp'] = unserialize (Settings::Get('smtp'));
$data['smtp_enabled'] = $data['smtp']->enabled;
$data['smtp_host'] = $data['smtp']->host;
$data['smtp_port'] = $data['smtp']->port;
$data['smtp_username'] = $data['smtp']->username;
$data['smtp_password'] = $data['smtp']->password;




/***********************
Handle form if submitted
***********************/

if (isset ($_POST['submitted'])) {

    // Validate video alerts
    if (isset ($_POST['alerts_videos']) && in_array ($_POST['alerts_videos'], array ('1', '0'))) {
        $data['alerts_videos'] = $_POST['alerts_videos'];
    } else {
        $errors['alerts_videos'] = 'Invalid video alert option';
    }


    // Validate video comment alerts
    if (isset ($_POST['alerts_comments']) && in_array ($_POST['alerts_comments'], array ('1', '0'))) {
        $data['alerts_comments'] = $_POST['alerts_comments'];
    } else {
        $errors['alerts_comments'] = 'Invalid video comments alert option';
    }


    // Validate video rating alerts
    if (isset ($_POST['alerts_ratings']) && in_array ($_POST['alerts_ratings'], array ('1', '0'))) {
        $data['alerts_ratings'] = $_POST['alerts_ratings'];
    } else {
        $errors['alerts_ratings'] = 'Invalid video ratings alert option';
    }


    // Validate user signup alerts
    if (isset ($_POST['alerts_users']) && in_array ($_POST['alerts_users'], array ('1', '0'))) {
        $data['alerts_users'] = $_POST['alerts_users'];
    } else {
        $errors['alerts_users'] = 'Invalid member signups alert option';
    }


    // Validate from email name
    if (!empty ($_POST['from_name'])) {
        if (!ctype_space ($_POST['from_name'])) {
            $data['from_name'] = htmlspecialchars (trim ($_POST['from_name']));
        } else {
            $errors['from_name'] = 'Invalid from name';
        }
    } else {
        $data['from_name'] = '';
    }

    
    // Validate from email address
    if (!empty ($_POST['from_address'])) {
        $pattern = '/^[a-z0-9][a-z0-9\.\-]+@[a-z0-9][a-z0-9\.\-]+$/i';
        if (preg_match ($pattern, $_POST['from_address'])) {
            $data['from_address'] = htmlspecialchars (trim ($_POST['from_address']));
        } else {
            $errors['from_address'] = 'Invalid from email address';
        }
    } else {
        $data['from_address'] = '';
    }


    // Validate smtp enabled option
    if (isset ($_POST['smtp_enabled']) && in_array ($_POST['smtp_enabled'], array ('1', '0'))) {

        $data['smtp']->enabled = $data['smtp_enabled'] = $_POST['smtp_enabled'];

        if ($data['smtp_enabled'] == 1) {
            $smtp_auth = true;
        } else {
            $smtp_auth = false;
            $data['smtp']->host = $data['smtp_host'] = '';
            $data['smtp']->port = $data['smtp_port'] = 25;
            $data['smtp']->password = $data['smtp_password'] = '';
            $data['smtp']->username = $data['smtp_username'] = '';
        }

    } else {
        $errors['smtp_enabled'] = 'Invalid SMTP enablement option';
    }

    
    // Validate smtp auth settings if enabled
    if ($smtp_auth) {
        
        // Validate smtp host
        $pattern = '/^[a-z0-9][a-z0-9\.\-]+$/i';
        if (!empty ($_POST['smtp_host']) && preg_match ($pattern, $_POST['smtp_host'])) {
            $data['smtp']->host = $data['smtp_host'] = htmlspecialchars (trim ($_POST['smtp_host']));
        } else {
            $errors['smtp_host'] = 'Invalid SMTP hostname';
        }


        // Validate smtp port
        if (isset ($_POST['smtp_port']) && is_numeric ($_POST['smtp_port'])) {
            $data['smtp']->port = $data['smtp_port'] = htmlspecialchars (trim ($_POST['smtp_port']));
        } else {
            $errors['smtp_port'] = 'Invalid SMTP port';
        }


        // Validate smtp username
        if (!empty ($_POST['smtp_username']) && !ctype_space ($_POST['smtp_username'])) {
            $data['smtp']->username = $data['smtp_username'] = htmlspecialchars (trim ($_POST['smtp_username']));
        } else {
            $errors['smtp_username'] = 'Invalid SMTP username';
        }


        // Validate smtp password
        if (!empty ($_POST['smtp_password']) && !ctype_space ($_POST['smtp_password'])) {
            $data['smtp']->password = $data['smtp_password'] = htmlspecialchars (trim ($_POST['smtp_password']));
        } else {
            $errors['smtp_password'] = 'Invalid SMTP password';
        }
        
    }

    

    // Update video if no errors were made
    if (empty ($errors)) {

        $data['smtp'] = serialize ($data['smtp']);
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

<div id="settings-email">

    <h1>Email Settings</h1>

    <?php if ($message): ?>
    <div class="<?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <div class="block">

        <form action="<?=ADMIN?>/settings_email.php" method="post">

            <div class="row-shift large">System Alerts</div>

            <div class="row <?=(isset ($errors['alerts_videos'])) ? ' errors' : ''?>">
                <label>New Video Alerts:</label>
                <select name="alerts_videos" class="dropdown">
                    <option value="1" <?=($data['alerts_videos']=='1')?'selected="selected"':''?>>Enabled</option>
                    <option value="0" <?=($data['alerts_videos']=='0')?'selected="selected"':''?>>Disabled</option>
                </select>
            </div>

            <div class="row <?=(isset ($errors['alerts_comments'])) ? ' errors' : ''?>">
                <label>Video Comment Alerts:</label>
                <select name="alerts_comments" class="dropdown">
                    <option value="1" <?=($data['alerts_comments']=='1')?'selected="selected"':''?>>Enabled</option>
                    <option value="0" <?=($data['alerts_comments']=='0')?'selected="selected"':''?>>Disabled</option>
                </select>
            </div>

            <div class="row <?=(isset ($errors['alerts_ratings'])) ? ' errors' : ''?>">
                <label>Video Rating Alerts:</label>
                <select name="alerts_ratings" class="dropdown">
                    <option value="1" <?=($data['alerts_ratings']=='1')?'selected="selected"':''?>>Enabled</option>
                    <option value="0" <?=($data['alerts_ratings']=='0')?'selected="selected"':''?>>Disabled</option>
                </select>
            </div>

            <div class="row <?=(isset ($errors['alerts_users'])) ? ' errors' : ''?>">
                <label>New Member Alerts:</label>
                <select name="alerts_users" class="dropdown">
                    <option value="1" <?=($data['alerts_users']=='1')?'selected="selected"':''?>>Enabled</option>
                    <option value="0" <?=($data['alerts_users']=='0')?'selected="selected"':''?>>Disabled</option>
                </select>
            </div>

            

            <div class="row-shift large">Email Configuration</div>

            <div class="row <?=(isset ($errors['from_name'])) ? ' errors' : ''?>">
                <label>"From" Name:</label>
                <input class="text" type="text" name="from_name" value="<?=$data['from_name']?>" /> <a class="more-info" title="If left blank, defaults to '<?=Settings::Get('sitename')?>'">More Info</a>
            </div>

            <div class="row <?=(isset ($errors['from_address'])) ? ' errors' : ''?>">
                <label>"From" Email Address:</label>
                <input class="text" type="text" name="from_address" value="<?=$data['from_address']?>" /> <a class="more-info" title="If left blank, defaults to 'cumulusclips@<?=$_SERVER['SERVER_NAME']?>'">More Info</a>
            </div>

            <div class="row <?=(isset ($errors['smtp_enabled'])) ? ' errors' : ''?>">
                <label>SMTP Authentication:</label>
                <select name="smtp_enabled" class="dropdown">
                    <option value="1" <?=($data['smtp_enabled']=='1')?'selected="selected"':''?>>Enabled</option>
                    <option value="0" <?=($data['smtp_enabled']=='0')?'selected="selected"':''?>>Disabled</option>
                </select>
            </div>

            <!-- BEGIN SMTP AUTH SETTINGS -->
            <div id="smtp_auth" class="<?=($data['smtp_enabled']=='0')?'hide':''?>">

                <div class="row <?=(isset ($errors['smtp_host'])) ? ' errors' : ''?>">
                    <label>SMTP Host:</label>
                    <input class="text" type="text" name="smtp_host" value="<?=$data['smtp_host']?>" />
                </div>

                <div class="row <?=(isset ($errors['smtp_port'])) ? ' errors' : ''?>">
                    <label>SMTP Port:</label>
                    <input class="text" type="text" name="smtp_port" value="<?=$data['smtp_port']?>" />
                </div>

                <div class="row <?=(isset ($errors['smtp_username'])) ? ' errors' : ''?>">
                    <label>SMTP Username:</label>
                    <input class="text" type="text" name="smtp_username" value="<?=$data['smtp_username']?>" />
                </div>

                <div class="row <?=(isset ($errors['smtp_password'])) ? ' errors' : ''?>">
                    <label>SMTP Password:</label>
                    <input class="text" type="text" name="smtp_password" value="<?=$data['smtp_password']?>" />
                </div>

            </div>
            <!-- END SMTP AUTH SETTINGS -->

            <div class="row-shift">
                <input type="hidden" name="submitted" value="TRUE" />
                <input type="submit" class="button" value="Update Settings" />
            </div>
        </form>

    </div>


</div>

<?php include ('footer.php'); ?>