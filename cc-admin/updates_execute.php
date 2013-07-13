<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');


// Establish page variables, objects, arrays, etc
Functions::RedirectIf ($logged_in = User::LoginCheck(), HOST . '/login/');
$admin = new User ($logged_in);
Functions::RedirectIf (User::CheckPermissions ('admin_panel', $admin), HOST . '/myaccount/');
$page_title = 'Update Complete!';
$tmp = DOC_ROOT . '/.updates';
$log = $tmp . '/status';
$error = null;


// Verify updates are available and user confirmed to begin update
$update = Functions::UpdateCheck();
if (!isset ($_GET['update']) || !$update) {
    header ("Location: " . ADMIN . '/updates.php');
}




try {

    /*****************
    INITIALIZE UPDATES
    *****************/

    ### Create hidden temp dir
    Filesystem::createDir($tmp);
    Filesystem::setPermissions($tmp, 0777);

    // Update log
    Filesystem::create($log);
    Filesystem::write($log, "<p>Initializing update&hellip;</p>\n");


    ### De-activate plugins
    ### De-activate themes





    /***************
    DOWNLOAD PACKAGE
    ***************/

    // Update log
    Filesystem::write($log, "<p>Downloading package&hellip;</p>\n");

    ### Download archive

    $curl_handle = curl_init();
    curl_setopt ($curl_handle, CURLOPT_URL, $update->location);
    curl_setopt ($curl_handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt ($curl_handle, CURLOPT_FOLLOWLOCATION, true);
    $zip_content = curl_exec ($curl_handle);
    curl_close ($curl_handle);

    $zip_file = $tmp . '/update.zip';
    Filesystem::create($zip_file);
    Filesystem::write($zip_file, $zip_content);
    if (md5_file ($zip_file) != $update->checksum) throw new Exception ("Error - Checksums don't match");



    ### Download patch file

    $curl_handle = curl_init();
    curl_setopt ($curl_handle, CURLOPT_URL, MOTHERSHIP_URL . '/updates/patches/?version=' . Functions::NumerizeVersion (CURRENT_VERSION));
    curl_setopt ($curl_handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt ($curl_handle, CURLOPT_FOLLOWLOCATION, true);
    $patch_file_content = curl_exec ($curl_handle);
    curl_close ($curl_handle);
    $patch_file = null;

    if (!empty ($patch_file_content)) {
        $patch_file = $tmp . '/patch_file.php';
        Filesystem::create($patch_file);
        Filesystem::write($patch_file, $patch_file_content);
    }





    /***********
    UNPACK FILES
    ***********/

    // Update log
    Filesystem::write($log, "<p>Unpacking files&hellip;</p>\n");

    ### Extracting files
    Filesystem::extract($zip_file);

    ### Load patch file into memory
    if ($patch_file) include_once ($patch_file);





    /************
    APPLY CHANGES
    ************/

    // Update log
    Filesystem::write($log, "<p>Applying changes&hellip;</p>\n");

    ### Applying changes
    Filesystem::copyDir($tmp . '/cumulusclips', DOC_ROOT);


    ### Perform patch file modifications
    if ($patch_file) {

        reset ($perform_update);
        foreach ($perform_update as $version) {

            ### Execute DB modifications queries
            $db_update_queries = call_user_func ('db_update_' . $version);
            foreach ($db_update_queries as $query) $db->Query ($query);


            ### Delete files marked for removal
            $remove_files = call_user_func ('remove_files_' . $version);
            foreach ($remove_files as $file) Filesystem::delete(DOC_ROOT . $file);

        }

    }





    /*******
    CLEAN UP
    *******/

    // Update log
    Filesystem::write($log, "<p>Clean up&hellip;</p>\n");

    ### Setting required permissions
    Filesystem::setPermissions(DOC_ROOT . '/cc-content/uploads', 0777);
    Filesystem::setPermissions(DOC_ROOT . '/cc-content/uploads/flv', 0777);
    Filesystem::setPermissions(DOC_ROOT . '/cc-content/uploads/mobile', 0777);
    Filesystem::setPermissions(DOC_ROOT . '/cc-content/uploads/thumbs', 0777);
    Filesystem::setPermissions(DOC_ROOT . '/cc-content/uploads/temp', 0777);
    Filesystem::setPermissions(DOC_ROOT . '/cc-content/uploads/avatars', 0777);
    Filesystem::setPermissions(DOC_ROOT . '/cc-core/logs', 0777);
    Filesystem::setPermissions(DOC_ROOT . '/cc-core/system/bin', 0777);
    Filesystem::setPermissions(DOC_ROOT . '/cc-core/system/bin/qtfaststart', 0777);
    Filesystem::setPermissions(DOC_ROOT . '/cc-core/system/qtfaststart', 0777);
    Filesystem::setPermissions(DOC_ROOT . '/cc-core/system/qtfaststart/exceptions.py', 0777);
    Filesystem::setPermissions(DOC_ROOT . '/cc-core/system/qtfaststart/__init__.py', 0777);
    Filesystem::setPermissions(DOC_ROOT . '/cc-core/system/qtfaststart/processor.py', 0777);

    ### Delete temp. dir.
    Filesystem::delete($tmp);


    ### Activate themes
    ### Activate plugins
    unset ($_SESSION['updates_available']);
    Settings::Set ('version', $update->version);

} catch (Exception $e) {
    $error = $e->getMessage();
    $page_title = 'Error During Update';
}



// Output Header
$dont_show_update_prompt = true;
include ('header.php');

?>

<div id="updates-complete">

    <?php if (!$error): ?>

        <h1>Update Complete!</h1>
        <div class="block">
            <p>You are now running the latest version of CumulusClips. Don't forget
            to re-enable all your plugins and themes.</p>
        </div>

    <?php else: ?>

        <h1>Error During Update</h1>
        <div class="block"><?=$error?></div>

    <?php endif; ?>

</div>

<?php include ('footer.php'); ?>