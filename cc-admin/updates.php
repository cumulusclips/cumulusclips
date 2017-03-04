<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$authService->enforceTimeout(true);

// Verify user can access admin panel
$userService = new \UserService();
Functions::RedirectIf($userService->checkPermissions('manage_settings', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$message = null;
$page_title = 'Updates';
$update = Functions::updateCheck();


// Output Header
$dont_show_update_prompt = true;
$pageName = 'updates';
include ('header.php');

?>

<h1>Update CumulusClips</h1>

<?php if ($update): ?>

    <p>An updated version of CumulusClips (version <?=$update->version?>) is available!</p>
    <p>Steps you can take:</p>
    <ol>
        <li>
            <strong>Update Automatically</strong> - CumulusClips will perform the update on
            it's own. You can just sit back and relax while it completes.
            <em>(Recommended)</em>
        </li>
        <li>
            <strong>Update Manually</strong> - Download version <?=$update->version?> from our
            website. Then manually extract and overwrite the files.
            This is usually done to recover from failed updates.
            <p>For detailed instructions on how to update manually you can reference our <a href="http://cumulusclips.org/docs/">documentation</a>.</p>
        </li>
    </ol>
    <p>
        <a class="button" href="<?=ADMIN?>/updates_begin.php">Update Automatically</a>
        <a class="button" href="http://cumulusclips.org/download/">Update Manually</a>
    </p>

<?php else: ?>
    <p>Everything looks good. Your system is up-to-date!</p>
<?php endif; ?>

<?php include ('footer.php'); ?>