<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$authService->enforceTimeout(true);

// Verify user can access admin panel
$userService = new \UserService();
Functions::redirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$userMapper = new userMapper();
$message = null;
$page_title = 'Video Imports';

$importList = array();

// Load existing import jobs
foreach (glob(UPLOAD_PATH . '/temp/import-*') as $import) {
    preg_match('/import\-([a-z0-9]+)$/i', $import, $matches);
    $importJobId = $matches[1];
    $importList[$importJobId] = \ImportManager::getManifest($importJobId);
}

// Handle clear if requested
if (isset($_GET['clear'])) {
    foreach ($importList as $key => $import) {
        if ($import->status == \ImportManager::JOB_COMPLETED) {
            \ImportManager::removeImport($key);
            unset($importList[$key]);
        }
    }
    $message = 'Completed import jobs have been removed';
    $message_type = 'alert-success';
    reset($importList);
}

// Handle restart request
if (!empty($_GET['restart']) && file_exists(UPLOAD_PATH . '/temp/import-' . $_GET['restart'])) {

    if ($importList[$_GET['restart']]->status === \ImportManager::JOB_COMPLETED_FAILURES) {

        try {
            \ImportManager::restartImport($_GET['restart']);
            $importList[$_GET['restart']] = \ImportManager::getManifest($_GET['restart']);
            $message = 'Import job ' . $_GET['restart'] . ' has been restarted';
            $message_type = 'alert-success';
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            $message_type = 'alert-danger';
        }
    }
}

// Handle delete request
if (!empty($_GET['delete']) && file_exists(UPLOAD_PATH . '/temp/import-' . $_GET['delete'])) {
    try {
        \ImportManager::removeImport($_GET['delete']);
        unset($importList[$_GET['delete']]);
        $message = 'Import job ' . $_GET['delete'] . ' has been removed';
        $message_type = 'alert-success';
    } catch (\Exception $exception) {
        $message = $exception->getMessage();
        $message_type = 'alert-danger';
    }
}

// Output Header
$pageName = 'videos-imports';
include('header.php');

?>

<h1>Video Imports</h1>

<div class="filters">
    <a href="<?php echo ADMIN; ?>/videos_imports_create.php" class="button">Create New Import</a>
    <?php if (count($importList) > 0): ?>
        <a
            href="<?php echo ADMIN; ?>/videos_imports.php?clear"
            class="button pull-right confirm"
            data-confirm="Import jobs that have completed and their associated files, will be removed. Do you wish to continue?"
        >Clear Completed</a>
    <?php endif; ?>
</div>

<?php if ($message): ?>
<div class="alert <?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<?php if (count($importList) > 0): ?>

    <?php foreach ($importList as $jobId => $import): ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="row">
            <div class="col-xs-6">
                <h3 class="panel-title">
                    Import Job ID: <?php echo $jobId; ?>

                </h3>

                <p>Started On: <?php echo \Functions::gmtToLocal($import->dateCreated, 'M d, Y g:i A T'); ?></p>
                <p>
                    Duration:
                    <?php if ($import->status === \ImportManager::JOB_PROGRESS): ?>
                        <span class="time-since" data-start="<?php echo $import->dateCreated . ' UTC'; ?>">
                            <?php $dateStart = new \DateTime($import->dateCreated, new \DateTimeZone('UTC')); ?>
                            <?php echo \Functions::getTimeSince($dateStart); ?>
                        </span>
                    <?php else: ?>
                        <?php
                            $dateStart = new \DateTime($import->dateCreated, new \DateTimeZone('UTC'));
                            $dateCompleted = new \DateTime($import->dateCompleted, new \DateTimeZone('UTC'));
                            echo \Functions::getTimeSince($dateStart, $dateCompleted);
                        ?>
                    <?php endif; ?>
                </p>
                <p>Started By: <?php echo $userMapper->getUserById($import->userId)->username; ?></p>

            </div>

            <div class="col-xs-6 text-right job-status">

                <p>
                    <span class="panel-title">
                        <?php if ($import->status === \ImportManager::JOB_COMPLETED): ?>
                            <i class="fa fa-check"></i> Completed
                        <?php elseif ($import->status === \ImportManager::JOB_COMPLETED_FAILURES): ?>
                            <i class="fa fa-exclamation-circle"></i> Completed with Failures
                        <?php elseif ($import->status === \ImportManager::JOB_PROGRESS): ?>
                            <i class="fa fa-refresh fa-spin"></i> In Progress
                        <?php endif; ?>
                    </span>

                    <?php if ($import->status === \ImportManager::JOB_COMPLETED_FAILURES): ?>
                        <a href="<?php echo ADMIN; ?>/videos_imports.php?restart=<?php echo $jobId; ?>" class="fa fa-repeat" title="Restart Import Job"></a>
                    <?php endif; ?>

                    <?php if ($import->status !== \ImportManager::JOB_PROGRESS): ?>
                        <a
                            href="<?php echo ADMIN; ?>/videos_imports.php?delete=<?php echo $jobId; ?>"
                            class="fa fa-trash delete confirm"
                            data-confirm="Import job and associated files will be permanently delete. Do you wish to continue?"
                            title="Delete Import Job"
                        ></a>
                    <?php endif; ?>

                </p>
                <p></p>

            </div>
            </div>

        </div>
        <div class="panel-body">

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Filesize</th>
                        <th>Filename</th>
                        <th>Title</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($import->videos as $key => $video): ?>
                    <tr>
                        <td>
                            <?php if ($video->status === \ImportManager::VIDEO_COMPLETED): ?>
                                <i class="fa fa-check"></i> Completed
                            <?php elseif ($video->status === \ImportManager::VIDEO_FAILED): ?>
                                <i class="fa fa-exclamation-triangle"></i> Failed
                            <?php elseif ($video->status === \ImportManager::VIDEO_QUEUED): ?>
                                <i class="fa fa-hourglass-half"></i> Queued
                            <?php elseif ($video->status === \ImportManager::VIDEO_TRANSCODING): ?>
                                <i class="fa fa-refresh fa-spin"></i> Transcoding
                            <?php endif; ?>
                        </td>
                        <td><?php echo \Functions::formatBytes(filesize(UPLOAD_PATH . '/temp/import-' . $jobId . '/' . $video->file), 1); ?></td>
                        <td><?php echo \Functions::cutOff($video->file, 30); ?></td>
                        <td><?php echo \Functions::cutOff($video->meta->title, 40); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </div>
    <?php endforeach; ?>

<?php else: ?>
    <p>No video imports found</p>
<?php endif; ?>


<?php include('footer.php'); ?>