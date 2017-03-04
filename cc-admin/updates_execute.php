<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify user can access admin panel
$userService = new \UserService();
Functions::redirectIf($userService->checkPermissions('manage_settings', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$page_title = 'Update Complete!';
$pageName = 'updates-complete';
$tmp = DOC_ROOT . '/.updates';
$log = $tmp . '/status';
$error = null;

// Verify updates are available and user confirmed to begin update
$update = Functions::updateCheck();
if (!isset($_GET['update']) || !$update) {
    header("Location: " . ADMIN . '/updates.php');
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
    curl_setopt($curl_handle, CURLOPT_URL, $update->location);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true);
    $zip_content = curl_exec($curl_handle);
    curl_close($curl_handle);

    $zip_file = $tmp . '/update.zip';
    Filesystem::create($zip_file);
    Filesystem::write($zip_file, $zip_content);
    if (md5_file($zip_file) != $update->checksum) throw new Exception("Error - Checksums don't match");



    ### Download patch file

    $curl_handle = curl_init();
    curl_setopt($curl_handle, CURLOPT_URL, MOTHERSHIP_URL . '/updates/patches/?version=' . urlencode(CURRENT_VERSION));
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true);
    $patch_file_content = curl_exec($curl_handle);
    curl_close($curl_handle);
    $patch_file = null;

    if (!empty($patch_file_content)) {
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
    if ($patch_file) include_once($patch_file);





    /************
    APPLY CHANGES
    ************/

    // Update log
    Filesystem::write($log, "<p>Applying changes&hellip;</p>\n");

    ### Applying changes
    Filesystem::copyDir($tmp . '/cumulusclips', DOC_ROOT);


    ### Perform patch file modifications
    if ($patch_file) {

        reset($perform_update);
        foreach ($perform_update as $version) {

            ### Execute DB modifications queries
            $db_update_queries = call_user_func('db_update_' . $version);
            foreach ($db_update_queries as $query) $db->query($query);


            ### Delete files marked for removal
            $remove_files = call_user_func('remove_files_' . $version);
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
    Filesystem::setPermissions(DOC_ROOT . '/cc-content/uploads/h264', 0777);
    Filesystem::setPermissions(DOC_ROOT . '/cc-content/uploads/webm', 0777);
    Filesystem::setPermissions(DOC_ROOT . '/cc-content/uploads/theora', 0777);
    Filesystem::setPermissions(DOC_ROOT . '/cc-content/uploads/mobile', 0777);
    Filesystem::setPermissions(DOC_ROOT . '/cc-content/uploads/thumbs', 0777);
    Filesystem::setPermissions(DOC_ROOT . '/cc-content/uploads/temp', 0777);
    Filesystem::setPermissions(DOC_ROOT . '/cc-content/uploads/avatars', 0777);
    Filesystem::setPermissions(DOC_ROOT . '/cc-content/uploads/files', 0777);
    Filesystem::setPermissions(DOC_ROOT . '/cc-core/logs', 0777);
    Filesystem::setPermissions(DOC_ROOT . '/cc-core/system/bin', 0755);
    Filesystem::setPermissions(DOC_ROOT . '/cc-core/system/bin/ffmpeg-32-bit', 0755);
    Filesystem::setPermissions(DOC_ROOT . '/cc-core/system/bin/ffmpeg-32-bit/ffmpeg', 0755);
    Filesystem::setPermissions(DOC_ROOT . '/cc-core/system/bin/ffmpeg-32-bit/qt-faststart', 0755);
    Filesystem::setPermissions(DOC_ROOT . '/cc-core/system/bin/ffmpeg-64-bit', 0755);
    Filesystem::setPermissions(DOC_ROOT . '/cc-core/system/bin/ffmpeg-64-bit/ffmpeg', 0755);
    Filesystem::setPermissions(DOC_ROOT . '/cc-core/system/bin/ffmpeg-64-bit/qt-faststart', 0755);

    ### Delete temp. dir.
    Filesystem::delete($tmp);


    ### Activate themes
    ### Activate plugins
    unset ($_SESSION['updates_available']);
    Settings::set('version', $update->version);

} catch (Exception $e) {
    $error = $e->getMessage();
    $page_title = 'Error During Update';
}

// Output Header
$dont_show_update_prompt = true;
include('header.php');

?>

<?php if (!$error): ?>

    <h1>Update Complete!</h1>
    <p>You are now running the latest version of CumulusClips. Don't forget to re-enable all your plugins and themes.</p>

<?php else: ?>

    <h1>Error During Update</h1>
    <p><?=$error?></p>

<?php endif; ?>

<?php include('footer.php'); ?>