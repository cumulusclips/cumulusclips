<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$userService = new UserService();
$adminUser = $userService->loginCheck();
Functions::RedirectIf($adminUser, HOST . '/login/');
Functions::RedirectIf($userService->checkPermissions('manage_settings', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$page_title = 'Customizations';
$errors = null;
$message = null;
$messageType = null;
$type = null;

// Handle form if submitted
if (isset($_POST['submitted'])) {
    
    // Validate type
    if (!empty($_POST['type']) && in_array($_POST['type'], array('css', 'js'))) {
        $type = ($_POST['type'] == 'js') ? 'custom_js' : 'custom_css';
        $friendlyType = ($_POST['type'] == 'js') ? 'JavaScript' : 'CSS';
    } else {
        $errors = true;
    }
    
    // Validate customization content
    if (empty($_POST['content'] )) {
        $content = '';
    } else {
        $content = $_POST['content'];
    }
    
    // Update customizations if no errors were made
    if (!$errors) {
        Settings::set($type, $content);
        $messageType = 'alert-success';
        $message = 'Your ' . $friendlyType . ' customizations have been updated';
    } else {
        $messageType = 'alert-danger';
        $message = 'Invalid customization type';
    }
}

// Output Header
$pageName = 'customizations';
include ('header.php');

?>

<h1>Customizations</h1>

<?php if ($message): ?>
<div class="alert <?=$messageType?>"><?=$message?></div>
<?php endif; ?>

<div role="tabpanel">
    <!-- Nav tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <li class="<?=(empty($type) || $type == 'custom_css') ? 'active' : ''?>"><a href="#custom_css" data-toggle="tab">CSS</a></li>
        <li class="<?=($type == 'custom_js') ? 'active' : ''?>"><a href="#custom_js" data-toggle="tab">JavaScript</a></li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <div class="tab-pane <?=(empty($type) || $type == 'custom_css') ? 'active' : ''?>" id="custom_css">
            <h3>Custom CSS</h3>
            <form action="" method="post">
                <textarea class="form-control" name="content"><?=htmlspecialchars(Settings::get('custom_css'))?></textarea>
                <input type="hidden" name="type" value="css" />
                <input type="hidden" name="submitted" value="true" />
                <input type="submit" class="button" value="Update CSS" />
            </form>
        </div>
        <div class="tab-pane <?=($type == 'custom_js') ? 'active' : ''?>" id="custom_js">
            <h3>Custom JavaScript</h3>
            <form action="" method="post">
                <textarea class="form-control" name="content"><?=htmlspecialchars(Settings::get('custom_js'))?></textarea>
                <input type="hidden" name="submitted" value="true" />
                <input type="hidden" name="type" value="js" />
                <input type="submit" class="button" value="Update JavaScript" />
            </form>
        </div>
    </div>
</div>

<?php include ('footer.php'); ?>