<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$authService->enforceTimeout(true);

// Verify user can access admin panel
$userService = new \UserService();
Functions::RedirectIf($userService->checkPermissions('manage_settings', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$page_title = 'Email Settings';
$data = array();
$errors = array();
$message = null;
$messageType = null;
$activeTab = null;
$textMapper = new TextMapper();
$openPanel = null;
$templates = array();

$config = Registry::get('config');
$data['alerts_videos'] = Settings::get('alerts_videos');
$data['alerts_comments'] = Settings::get('alerts_comments');
$data['alerts_users'] = Settings::get('alerts_users');
$data['alerts_flags'] = Settings::get('alerts_flags');
$data['alerts_imports'] = Settings::get('alerts_imports');
$data['from_name'] = $config->from_name;
$data['from_address'] = $config->from_address;
$data['smtp'] = $config->smtp;
$data['smtp_enabled'] = $data['smtp']->enabled;
$data['smtp_host'] = $data['smtp']->host;
$data['smtp_port'] = $data['smtp']->port;
$data['smtp_username'] = $data['smtp']->username;
$data['smtp_password'] = $data['smtp']->password;

// Load system email templates
$mailer = new Mailer($config);
foreach (glob(DOC_ROOT . '/cc-content/emails/*.tpl') as $templatePath) {

    $templateName = basename($templatePath, '.tpl');
    $template = new stdClass();
    $template->mailerTemplate = $mailer->getTemplate($templateName);
    $templates[$templateName] = $template;

    // Load email subject
    $template->customSubject = $textMapper->getByCustom(array(
        'type' => TextMapper::TYPE_SUBJECT,
        'language' => 'english',
        'name' => $templateName
    ));
    $template->subject = ($template->customSubject) ? $template->customSubject->content : $template->mailerTemplate->subject;

    // Load email body
    $template->customBody = $textMapper->getByCustom(array(
        'type' => TextMapper::TYPE_EMAIL_TEXT,
        'language' => 'english',
        'name' => $templateName
    ));
    $template->body = ($template->customBody) ? $template->customBody->content : $template->mailerTemplate->body;
}

// Handle reset template if requested
if (isset($_GET['reset']) && isset($templates[$_GET['reset']])) {

    $activeTab = 'templates';
    $template = $templates[$_GET['reset']];

    // Remove custom subject if it exists
    if ($template->customSubject) {
        $template->subject = $template->mailerTemplate->subject;
        $textMapper->delete($template->customSubject->textId);
        $template->customSubject = false;
    }

    // Remove custom body if it exists
    if ($template->customBody) {
        $template->body = $template->mailerTemplate->body;
        $textMapper->delete($template->customBody->textId);
        $template->customBody = false;
    }

    $message = '"' . $template->mailerTemplate->name . '" has been reset.';
    $messageType = 'alert-success';
}

// Handle template form if submitted
if (isset($_POST['submitted_templates'])) {

    $activeTab = 'templates';

    // Validate form nonce token and submission speed
    if (
        !empty($_POST['nonce'])
        && !empty($_SESSION['formNonce'])
        && !empty($_SESSION['formTime'])
        && $_POST['nonce'] == $_SESSION['formNonce']
        && time() - $_SESSION['formTime'] >= 2
    ) {
        // Verify valid template was submitted
        if (!empty($_POST['template']) && isset($templates[$_POST['template']])) {
            $openPanel = $_POST['template'];
            $template = $templates[$openPanel];

            // Validate template subject
            if (!empty($_POST['subject'])) {
                $subject = trim($_POST['subject']);
            } else {
                $errors[] = 'Invalid email template subject line';
            }

            // Validate template body
            if (!empty($_POST['body'])) {
                $body = preg_replace("/\r\n|\r/", "\n", trim($_POST['body']));
            } else {
                $errors[] = 'Invalid email template body';
            }

            // Save template if no errors were found
            if (empty($errors)) {

                // Remove custom subject if given subject is same as original
                if ($subject === $template->mailerTemplate->subject && $template->customSubject) {
                    $textMapper->delete($template->customSubject->textId);
                    $template->customSubject = false;
                }

                // Save custom subject if it differs from original
                if ($subject !== $template->mailerTemplate->subject) {

                    // Create custom subject entry if it doesn't exist
                    if (!$template->customSubject) {
                        $template->customSubject = new Text();
                        $template->customSubject->type = TextMapper::TYPE_SUBJECT;
                        $template->customSubject->language = 'english';
                        $template->customSubject->name = $openPanel;
                    }

                    // Save custom subject
                    $template->customSubject->content = $subject;
                    $textId = $textMapper->save($template->customSubject);
                    $template->customSubject = $textMapper->getById($textId);
                }

                // Remove custom body if given body is same as original
                if ($body === $template->mailerTemplate->body && $template->customBody) {
                    $textMapper->delete($template->customBody->textId);
                    $template->customBody = false;
                }

                // Save custom body if it differs from original
                if ($body !== $template->mailerTemplate->body) {

                    // Create custom body entry if it doesn't exist
                    if (!$template->customBody) {
                        $template->customBody = new Text();
                        $template->customBody->type = TextMapper::TYPE_EMAIL_TEXT;
                        $template->customBody->language = 'english';
                        $template->customBody->name = $openPanel;
                    }

                    // Save custom body
                    $template->customBody->content = $body;
                    $textId = $textMapper->save($template->customBody);
                    $template->customBody = $textMapper->getById($textId);
                }

                $template->subject = $subject;
                $template->body = $body;
                $message = '"' . $template->mailerTemplate->name . '" has been updated.';
                $messageType = 'alert-success';

            } else {
                $message = 'The following errors were found. Please correct them and try again.';
                $message .= '<br /><br /> - ' . implode('<br /> - ', $errors);
                $messageType = 'alert-danger';
            }

        } else {
            $message = 'Invalid template';
            $messageType = 'alert-danger';
        }

    } else {
        $message = 'Expired or invalid session';
        $messageType = 'alert-danger';
    }
}

// Handle form if submitted
if (isset($_POST['submitted_alerts'])) {

    $activeTab = 'alerts';

    // Validate form nonce token and submission speed
    if (
        !empty($_POST['nonce'])
        && !empty($_SESSION['formNonce'])
        && !empty($_SESSION['formTime'])
        && $_POST['nonce'] == $_SESSION['formNonce']
        && time() - $_SESSION['formTime'] >= 2
    ) {
        // Validate video alerts
        if (isset($_POST['alerts_videos']) && in_array($_POST['alerts_videos'], array('1', '0'))) {
            $data['alerts_videos'] = $_POST['alerts_videos'];
        } else {
            $errors['alerts_videos'] = 'Invalid video alert option';
        }

        // Validate video imports alerts
        if (isset($_POST['alerts_imports']) && in_array($_POST['alerts_imports'], array('1', '0'))) {
            $data['alerts_imports'] = $_POST['alerts_imports'];
        } else {
            $errors['alerts_imports'] = 'Invalid video imports alert option';
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

        // Update video if no errors were made
        if (empty($errors)) {
            $data['smtp'] = json_encode($data['smtp']);
            foreach ($data as $key => $value) {
                Settings::set($key, $value);
            }
            $data['smtp'] = json_decode($data['smtp']);
            $message = 'Settings have been updated.';
            $messageType = 'alert-success';
        } else {
            $message = 'The following errors were found. Please correct them and try again.';
            $message .= '<br /><br /> - ' . implode('<br /> - ', $errors);
            $messageType = 'alert-danger';
        }

    } else {
        $message = 'Expired or invalid session';
        $messageType = 'alert-danger';
    }
}

// Handle form if submitted
if (isset($_POST['submitted_config'])) {

    $activeTab = 'config';

    // Validate form nonce token and submission speed
    if (
        !empty($_POST['nonce'])
        && !empty($_SESSION['formNonce'])
        && !empty($_SESSION['formTime'])
        && $_POST['nonce'] == $_SESSION['formNonce']
        && time() - $_SESSION['formTime'] >= 2
    ) {
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
            $messageType = 'alert-success';
        } else {
            $message = 'The following errors were found. Please correct them and try again.';
            $message .= '<br /><br /> - ' . implode('<br /> - ', $errors);
            $messageType = 'alert-danger';
        }

    } else {
        $message = 'Expired or invalid session';
        $messageType = 'alert-danger';
    }
}

// Generate new form nonce
$formNonce = md5(uniqid(rand(), true));
$_SESSION['formNonce'] = $formNonce;
$_SESSION['formTime'] = time();

// Output Header
$pageName = 'settings-email';
include('header.php');

?>

<h1>Email Settings</h1>

<div class="alert <?=$messageType?>"><?=$message?></div>

<!-- Nav tabs -->
<ul class="nav nav-tabs" role="tablist">
    <li class="<?=(!$activeTab || $activeTab == 'config') ? 'active' : ''?>"><a href="#config" data-toggle="tab">Configuration</a></li>
    <li class="<?=($activeTab == 'alerts') ? 'active' : ''?>"><a href="#alerts" data-toggle="tab">Alerts</a></li>
    <li class="<?=($activeTab == 'templates') ? 'active' : ''?>"><a href="#templates" data-toggle="tab">Templates</a></li>
</ul>

<!-- Tab panes -->
<div class="tab-content">

    <!-- BEGIN Configuration Tab -->
    <div class="tab-pane <?=(!$activeTab || $activeTab == 'config') ? 'active' : ''?>" id="config">

        <h3>Email Configuration</h3>

        <form action="<?=ADMIN?>/settings_email.php" method="post">

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

            <input type="hidden" name="submitted_config" value="TRUE" />
            <input type="hidden" name="nonce" value="<?=$formNonce?>" />
            <input type="submit" class="button" value="Update Settings" />

        </form>

    </div>
    <!-- END Configuration Tab -->

    <!-- BEGIN Alerts Tab -->
    <div class="tab-pane <?=($activeTab == 'alerts') ? 'active' : ''?>" id="alerts">

        <h3>System Alerts</h3>

        <form action="<?=ADMIN?>/settings_email.php" method="post">

            <div class="form-group <?=(isset ($errors['alerts_videos'])) ? 'has-error' : ''?>">
                <label class="control-label">New Video Alerts:</label>
                <select name="alerts_videos" class="form-control">
                    <option value="1" <?=($data['alerts_videos']=='1')?'selected="selected"':''?>>Enabled</option>
                    <option value="0" <?=($data['alerts_videos']=='0')?'selected="selected"':''?>>Disabled</option>
                </select>
            </div>

            <div class="form-group <?=(isset ($errors['alerts_imports'])) ? 'has-error' : ''?>">
                <label class="control-label">Video Import Complete Alerts:</label>
                <select name="alerts_imports" class="form-control">
                    <option value="1" <?=($data['alerts_imports']=='1')?'selected="selected"':''?>>Enabled</option>
                    <option value="0" <?=($data['alerts_imports']=='0')?'selected="selected"':''?>>Disabled</option>
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

            <input type="hidden" name="submitted_alerts" value="TRUE" />
            <input type="hidden" name="nonce" value="<?=$formNonce?>" />
            <input type="submit" class="button" value="Update Settings" />

        </form>

    </div>
    <!-- END Alerts Tab -->

    <!-- BEGIN Templates Tab -->
    <div class="tab-pane <?=($activeTab == 'templates') ? 'active' : ''?>" id="templates">

        <h3>Email Templates</h3>

        <div class="panel-group" id="accordion" role="tablist">


        <?php foreach ($templates as $template): ?>

            <!-- BEGIN <?=$template->mailerTemplate->name?> Panel -->
            <div class="panel panel-default">

                <div class="panel-heading clearfix" role="tab" id="heading-<?=$template->mailerTemplate->systemName?>">
                    <h4 class="panel-title pull-left">
                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#panel-<?=$template->mailerTemplate->systemName?>">
                            <span class="glyphicon glyphicon-<?=($template->mailerTemplate->systemName === $openPanel) ? 'minus' : 'plus'?>"></span>
                            <?=$template->mailerTemplate->name?>
                        </a>
                    </h4>
                    <a class="pull-right delete" href="<?=ADMIN?>/settings_email.php?reset=<?=$template->mailerTemplate->systemName?>">Reset</a>
                </div>

                <div id="panel-<?=$template->mailerTemplate->systemName?>" class="panel-collapse collapse <?=($template->mailerTemplate->systemName === $openPanel) ? 'in' : ''?>" role="tabpanel">
                    <div class="panel-body">

                        <form action="<?=ADMIN?>/settings_email.php" method="post">
                            <div class="form-group">
                                <label class="control-label">Subject</label>
                                <input type="text" class="form-control" name="subject" value="<?=$template->subject?>" />
                            </div>

                            <div class="form-group">
                                <label class="control-label">Body</label>
                                <textarea rows="7" cols="60" class="form-control" name="body"><?=$template->body?></textarea rows="7" cols="60">
                            </div>

                            <input type="hidden" name="template" value="<?=$template->mailerTemplate->systemName?>" />
                            <input type="hidden" name="submitted_templates" value="TRUE" />
                            <input type="hidden" name="nonce" value="<?=$formNonce?>" />
                            <input type="submit" class="button" value="Save Template" />
                        </form>

                    </div>
                </div>

            </div>
            <!-- END <?=$template->mailerTemplate->name?> Panel -->

        <?php endforeach; ?>
        </div>

    </div>
    <!-- END Templates Tab -->

</div>

<?php include ('footer.php'); ?>