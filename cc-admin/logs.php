<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');

// Establish page variables, objects, arrays, etc
Functions::RedirectIf ($logged_in = User::LoginCheck(), HOST . '/login/');
$admin = new User ($logged_in);
Functions::RedirectIf (User::CheckPermissions ('admin_panel', $admin), HOST . '/myaccount/');
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
                <a style="margin-left:20px;" class="delete confirm" href="<?=ADMIN?>/logs.php?purge=all" data-confirm="You're about to delete all log files. This cannot be undone. Do you want to proceed?">Purge Logs</a>
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