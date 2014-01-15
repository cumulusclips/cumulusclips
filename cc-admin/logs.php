<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/config/admin.bootstrap.php');

// Verify if user is logged in
$userService = new UserService();
$adminUser = $userService->loginCheck();
Functions::RedirectIf($adminUser, HOST . '/login/');
Functions::RedirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/myaccount/');

// Establish page variables, objects, arrays, etc
$logFileContents = null;
$page_title = 'System Logs';
$message = null;

// Retrieve available logs
$contents = scandir(LOG);
foreach ($contents as $key => $file) {
    if (!preg_match('/\.log$/i', $file)) {
        unset($contents[$key]);
    }
}

// Display log file when requested
if (!empty($_GET['log']) && in_array($_GET['log'], $contents) && file_exists(LOG . '/' . $_GET['log'])) {
    $logFileContents = file_get_contents(LOG . '/' . $_GET['log']);
}

// Purge all log files when requested
if (isset($_GET['purge']) && $_GET['purge'] == 'all' && !empty($contents)) {
    try {
        // Delete files
        foreach (glob(LOG . '/*.log') as $log) {
            Filesystem::delete($log);
        }
        $messageType = 'success';
        $message = 'Logs have been successfully purged.';
        $contents = array();
    } catch (Exception $e) {
        $messageType = 'error';
        $message = 'Unable to delete log files.';
        App::Alert('Error During Log File Removal', "Unable to delete log files. Error: " . $e->getMessage());
    }
}

// Output Header
include ('header.php');

?>
<div id="logs">

    <h1>System Logs</h1>
    
    <?php if ($message): ?>
    <div class="message <?=$messageType?>"><?=$message?></div>
    <?php endif; ?>

    <div class="block">
        <?php if (!empty($contents)): ?>
            <form action="<?=ADMIN?>/logs.php" method="get">
                <strong>Logs Files:</strong>
                <select name="log" class="dropdown">
                    <option>-- Select Log --</option>
                    <?php foreach($contents as $logFile): ?>
                        <option><?=$logFile?></option>
                    <?php endforeach; ?>
                </select>
                <input type="submit" value="View Log" class="button" />
                <a style="margin-left:20px;" class="delete confirm" href="<?=ADMIN?>/logs.php?purge=all" data-confirm="You're about to delete all log files. This cannot be undone. Do you want to proceed?">Purge All Logs</a>
            </form>
        <?php else: ?>
            <p>No log files available.</p>
        <?php endif; ?>
            
        <?php if ($logFileContents): ?>
            <pre><?=$logFileContents?></pre>
        <?php endif; ?>
    </div>

</div>

<?php include ('footer.php'); ?>