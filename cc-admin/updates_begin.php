<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify user can access admin panel
$userService = new \UserService();
Functions::RedirectIf($userService->checkPermissions('manage_settings', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$message = null;
$page_title = 'Begin Update';
$pageName = 'updates-begin';
$header = 'Begin Update';


// Check for update
$update = Functions::UpdateCheck();
if (!$update) {
    header ("Location: " . ADMIN . '/updates.php');
}

// Output Header
$dont_show_update_prompt = true;
include ('header.php');

?>

<div id="pre-updates">
    <h1>Begin Update</h1>

    <p>You're about to update your system. Your site will be unusable during
    this process and any visitors will see a 'Maintenance Mode' message.</p>

    <p>Be sure to backup you database and any changes made to your system
    before you begin the update.</p>

    <p><a class="button begin-update" href="<?=ADMIN?>/updates_execute.php?update=<?=time()?>">Click to Begin Update</a></p>
</div>

<div id="updates-in-progress">
    <h1>Update in Progress&hellip;</h1>

    <p>CumulusClips is currently performing updates. <strong>DO NOT</strong>
    close or refresh this page. Doing so will cause incomplete or even
    failed installation and you will have to manually update.</p>

    <p>This page may <em>seem</em> unresponsive however it is working in the
    background, we promise.</p>

    <div class="status"><p>Initializing update&hellip;</p></div>
</div>

<?php include ('footer.php'); ?>