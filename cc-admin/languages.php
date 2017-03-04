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
$page_title = 'Languages';
$lang_list = array();
$admin_js[] = ADMIN . '/extras/fancybox/jquery.fancybox-1.3.4.js';
$admin_js[] = ADMIN . '/js/fancybox.js';
$missing = array();
$languageList = array();
$availableLanguages = array();
$installedLanguages = Language::getInstalled();
$defaultLanguage = Settings::get('default_language');

// Handle "Install" language if requested
if (
    !empty($_GET['install'])
    && !isset($installedLanguages->{$_GET['install']})
) {
    $installedLanguages = Language::install($_GET['install']);
    $message = $installedLanguages->{$_GET['install']}->lang_name . ' has been installed.';
    $messageType = 'alert-success';
}

// Handle "Uninstall" language if requested
if (!empty($_GET['uninstall'])) {
    try {
        $languageName = $installedLanguages->{$_GET['uninstall']}->lang_name;
        $installedLanguages = Language::uninstall($_GET['uninstall']);
        $message = $languageName . ' has been uninstalled.';
        $messageType = 'alert-success';
    } catch (Exception $exception) {
        $message = $exception->getMessage();
        $messageType = 'alert-danger';
    }
}

// Handle "Activate" language if requested
if (
    !empty($_GET['activate'])
    && isset($installedLanguages->{$_GET['activate']})
    && !$installedLanguages->{$_GET['activate']}->active
) {
    $installedLanguages->{$_GET['activate']}->active = true;
    Language::save($installedLanguages);
    $message = $installedLanguages->{$_GET['activate']}->lang_name . ' has been activated.';
    $messageType = 'alert-success';
}

// Handle "Deactivate" language if requested
if (
    !empty($_GET['deactivate'])
    && isset($installedLanguages->{$_GET['deactivate']})
    && $installedLanguages->{$_GET['deactivate']}->active
) {
    $installedLanguages->{$_GET['deactivate']}->active = false;
    Language::save($installedLanguages);
    $message = $installedLanguages->{$_GET['deactivate']}->lang_name . ' has been deactivated.';
    $messageType = 'alert-success';
}

// Handle "Set Default" language if requested
if (
    !empty($_GET['default'])
    && isset($installedLanguages->{$_GET['default']})
    && $defaultLanguage !== $_GET['default']
) {
    Settings::set('default_language', $_GET['default']);
    $defaultLanguage = $_GET['default'];
    $message = $installedLanguages->{$_GET['default']}->lang_name . ' is now the default language.';
    $messageType = 'alert-success';
}

// Build list of available languages
foreach ($installedLanguages as $systemName => $language) {

    $installedLanguageFile = DOC_ROOT . '/cc-content/languages/' . $systemName . '.xml';
    if (!file_exists($installedLanguageFile)) {
        $installedLanguages->{$systemName}->active = false;
        $isMissing = true;
        $missing[] = $systemName;
    } else {
        $isMissing = null;
    }

    $languageList[$systemName] = (object) array(
        'installed' => true,
        'missing' => $isMissing,
        'information' => $language
    );
}

// Add uninstalled languages files to list of available languages
foreach (glob(DOC_ROOT . '/cc-content/languages/*.xml') as $availableLanguageFile) {
    $systemName = basename($availableLanguageFile, '.xml');
    if (!isset($installedLanguages->{$systemName})) {
        $xml = simplexml_load_file($availableLanguageFile);
        $languageList[$systemName] = (object) array(
            'installed' => false,
            'missing' => null,
            'information' => $xml->information
        );
    }
}

// Detect if any languages pack files are missing and display warning
if (!empty($missing)) {
    Language::save($installedLanguages);
    $message = 'Some installed languages files are missing (in /cc-content/languages). As a result they have been disabled.';
    $messageType = 'alert-warning';
}

// Output Header
$pageName = 'languages';
include ('header.php');

?>

<link rel="stylesheet" type="text/css" href="<?=ADMIN?>/extras/fancybox/jquery.fancybox-1.3.4.css" />

<h1>Languages</h1>

<?php if ($message): ?>
<div class="alert <?=$messageType?>"><?=$message?></div>
<?php endif; ?>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Language</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>


<?php foreach ($languageList as $systemName => $language): ?>

    <tr>
        <td>
            <p class="h3"><?=($language->missing) ? '(Disabled) ' : ''?><?=$language->information->lang_name?></p>

            <p>
            <?php if ($language->installed): ?>

                <?php if ($systemName === $defaultLanguage): ?>
                    <strong>Default Language</strong>
                    &nbsp;&nbsp;|&nbsp;&nbsp; <a href="<?=ADMIN?>/languages_edit.php?language=<?=$systemName?>">Edit</a>
                <?php elseif ($language->missing): ?>
                    <a href="<?=ADMIN?>/languages.php?uninstall=<?=$systemName?>" class="delete confirm" data-confirm="This will completely uninstall and remove this language from your system. Do you want to proceed?">Uninstall</a>
                <?php elseif ($language->information->active): ?>
                    <a href="<?=ADMIN?>/languages_edit.php?language=<?=$systemName?>">Edit</a>
                    &nbsp;&nbsp;|&nbsp;&nbsp; <a href="<?=ADMIN?>/languages.php?default=<?=$systemName?>">Set Default</a>
                    &nbsp;&nbsp;|&nbsp;&nbsp; <a href="<?=ADMIN?>/languages.php?deactivate=<?=$systemName?>">Deactivate</a>
                    <!-- &nbsp;&nbsp;|&nbsp;&nbsp; <a href="<?=HOST?>/?preview_lang=<?=$systemName?>" class="iframe">Preview</a> -->

                    <?php if ($systemName !== 'en_US'): ?>
                        &nbsp;&nbsp;|&nbsp;&nbsp; <a href="<?=ADMIN?>/languages.php?uninstall=<?=$systemName?>" class="delete confirm" data-confirm="This will completely uninstall and remove this language from your system. Do you want to proceed?">Uninstall</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?=ADMIN?>/languages_edit.php?language=<?=$systemName?>">Edit</a>
                    &nbsp;&nbsp;|&nbsp;&nbsp; <a href="<?=ADMIN?>/languages.php?activate=<?=$systemName?>">Activate</a>
                    &nbsp;&nbsp;|&nbsp;&nbsp; <a href="<?=HOST?>/?preview_lang=<?=$systemName?>" class="iframe">Preview</a>

                    <?php if ($systemName !== 'en_US'): ?>
                        &nbsp;&nbsp;|&nbsp;&nbsp; <a href="<?=ADMIN?>/languages.php?uninstall=<?=$systemName?>" class="delete confirm" data-confirm="This will completely uninstall and remove this language from your system. Do you want to proceed?">Uninstall</a>
                    <?php endif; ?>
                <?php endif; ?>

            <?php else: ?>
                <a href="<?=ADMIN?>/languages.php?install=<?=$systemName?>">Install</a>
            <?php endif; ?>
            </p>

        </td>

        <?php if ($language->missing): ?>
            <td>Language file has been disabled.</td>
        <?php else: ?>
            <td>
                <p><strong>Sample:</strong> <?=$language->information->sample?></p>
                <?php if (!empty($language->information->author)): ?>
                    <p>By: <?=$language->information->author?></p>
                <?php endif; ?>

                <?php if (!empty($language->information->notes)): ?>
                    <p><?=$language->information->notes?></p>
                <?php endif; ?>
            </td>
        <?php endif; ?>
    </tr>

<?php endforeach; ?>
    </tbody>
</table>

<?php include('footer.php'); ?>