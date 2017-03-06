<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$authService->enforceTimeout(true);

// Verify user can access admin panel
$userService = new \UserService();
Functions::RedirectIf($userService->checkPermissions('manage_settings', $adminUser), HOST . '/account/');

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
    $activeLog = $_GET['log'];
    $logFileContents = file_get_contents(LOG . '/' . $_GET['log']);
} else {
    $activeLog = '-- Select Log --';
}

// Purge all log files when requested
if (isset($_GET['purge']) && $_GET['purge'] == 'all' && !empty($contents)) {
    try {
        // Delete files
        foreach (glob(LOG . '/*.log') as $log) {
            Filesystem::delete($log);
        }
        $messageType = 'alert-success';
        $message = 'Logs have been successfully purged.';
        $contents = array();
    } catch (Exception $e) {
        $messageType = 'alert-danger';
        $message = 'Unable to delete log files.';
        App::Alert('Error During Log File Removal', "Unable to delete log files. Error: " . $e->getMessage());
    }
}

// Output Header
$pageName = 'logs';
include ('header.php');

?>

<h1>System Logs</h1>

<?php if ($message): ?>
<div class="alert <?=$messageType?>"><?=$message?></div>
<?php endif; ?>

<?php if (!empty($contents)): ?>
        <div class="dropdown">
          <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
            <?=htmlspecialchars($activeLog)?>
            <span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
            <?php foreach($contents as $logFile): ?>
                <li class="<?=($logFile == $activeLog) ? 'active' : ''?>"><a tabindex="-1" href="<?=ADMIN?>/logs.php?log=<?=$logFile?>"><?=$logFile?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>

        <a style="margin-left:20px;" class="delete confirm" href="<?=ADMIN?>/logs.php?purge=all" data-confirm="You're about to delete all log files. This cannot be undone. Do you want to proceed?">Purge All Logs</a>
<?php else: ?>
    <p>No log files available.</p>
<?php endif; ?>

<?php if ($logFileContents): ?>
    <pre><?=$logFileContents?></pre>
<?php endif; ?>

<?php include ('footer.php'); ?>