<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');


// Establish page variables, objects, arrays, etc
Functions::RedirectIf ($logged_in = User::LoginCheck(), HOST . '/login/');
$admin = new User ($logged_in);
Functions::RedirectIf (User::CheckPermissions ('admin_panel', $admin), HOST . '/myaccount/');
$page_title = 'General Settings';
$data = array();
$errors = array();
$message = null;


$data['sitename'] = Settings::Get('sitename');
$data['base_url'] = Settings::Get('base_url');
$data['admin_email'] = Settings::Get('admin_email');
$data['auto_approve_videos'] = Settings::Get('auto_approve_videos');
$data['auto_approve_users'] = Settings::Get('auto_approve_users');
$data['auto_approve_comments'] = Settings::Get('auto_approve_comments');





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
        $message_type = 'errors';
    }

}


// Output Header
include ('header.php');

?>

<div id="settings">

    <h1>General Settings</h1>

    <?php if ($message): ?>
    <div class="<?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <div class="block">

        <form action="<?=ADMIN?>/settings.php" method="post">

            <div class="row <?=(isset ($errors['sitename'])) ? ' error' : ''?>">
                <label>Sitename:</label>
                <input class="text" type="text" name="sitename" value="<?=$data['sitename']?>" />
            </div>

            <div class="row <?=(isset ($errors['base_url'])) ? ' error' : ''?>">
                <label>Base URL:</label>
                <input class="text" type="text" name="base_url" value="<?=$data['base_url']?>" />
            </div>

            <div class="row <?=(isset ($errors['admin_email'])) ? ' error' : ''?>">
                <label>Admin Email:</label>
                <input class="text" type="text" name="admin_email" value="<?=$data['admin_email']?>" />
            </div>

            <div class="row <?=(isset ($errors['auto_approve_videos'])) ? ' error' : ''?>">
                <label>Video Approval:</label>
                <select name="auto_approve_videos" class="dropdown">
                    <option value="1" <?=($data['auto_approve_videos']=='1')?'selected="selected"':''?>>Auto-Approve</option>
                    <option value="0" <?=($data['auto_approve_videos']=='0')?'selected="selected"':''?>>Approval Required</option>
                </select>
            </div>

            <div class="row <?=(isset ($errors['auto_approve_users'])) ? ' error' : ''?>">
                <label>Member Approval:</label>
                <select name="auto_approve_users" class="dropdown">
                    <option value="1" <?=($data['auto_approve_users']=='1')?'selected="selected"':''?>>Auto-Approve</option>
                    <option value="0" <?=($data['auto_approve_users']=='0')?'selected="selected"':''?>>Approval Required</option>
                </select>
            </div>

            <div class="row <?=(isset ($errors['auto_approve_comments'])) ? ' error' : ''?>">
                <label>Comment Approval:</label>
                <select name="auto_approve_comments" class="dropdown">
                    <option value="1" <?=($data['auto_approve_comments']=='1')?'selected="selected"':''?>>Auto-Approve</option>
                    <option value="0" <?=($data['auto_approve_comments']=='0')?'selected="selected"':''?>>Approval Required</option>
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