<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');


// Establish page variables, objects, arrays, etc
Functions::RedirectIf ($logged_in = User::LoginCheck(), HOST . '/login/');
$admin = new User ($logged_in);
Functions::RedirectIf (User::CheckPermissions ('admin_panel', $admin), HOST . '/myaccount/');
$message = null;
$page_title = 'Updates';
$update = Functions::UpdateCheck();


// Output Header
$dont_show_update_prompt = true;
include ('header.php');

?>

<div id="updates">

    <h1>Update CumulusClips</h1>

    <?php if ($message): ?>
    <div class="message <?=$message_type?>"><?=$message?></div>
    <?php endif; ?>

    <?php if ($update): ?>

        <div class="block">
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
        </div>

    <?php else: ?>

        <div class="block">
            <p>Everything looks good. Your system is up-to-date!</p>
        </div>

    <?php endif; ?>


</div>

<?php include ('footer.php'); ?>