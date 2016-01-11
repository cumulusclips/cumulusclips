<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$userService = new UserService();
$adminUser = $userService->loginCheck();
Functions::RedirectIf($adminUser, HOST . '/login/');
Functions::RedirectIf($userService->checkPermissions('manage_settings', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$message = null;
$page_title = 'Languages';
$lang_list = array();
$admin_js[] = ADMIN . '/extras/fancybox/jquery.fancybox-1.3.4.js';
$admin_js[] = ADMIN . '/js/fancybox.js';
$active_languages = Language::getActiveLanguages();
$languageSystemNames = Functions::arrayColumn($active_languages, 'system_name');

// Remove any orphaned languages
foreach ($active_languages as $key => $language) {
    $language_file = DOC_ROOT . '/cc-content/languages/' . $language->system_name . '.xml';
    if (!file_exists($language_file)) {
        unset ($active_languages[$key]);
    }
}
Settings::set('active_languages', json_encode(array_values($active_languages)));

// Handle "Delete" language if requested
if (!empty($_GET['delete']) && !ctype_space($_GET['delete'])) {

    $language_file = DOC_ROOT . '/cc-content/languages/' . $_GET['delete'] . '.xml';
    if (file_exists($language_file) && $_GET['delete'] != Settings::get('default_language')) {

        // Deactivate language if applicable
        $key = array_search($_GET['delete'], $languageSystemNames);
        if ($key !== false) {
            unset($active_languages[$key]);
            Settings::set('active_languages', json_encode(array_values($active_languages)));
        }
        
        // Delete language file
        $xml = simplexml_load_file($language_file);
        $message = $xml->information->lang_name . ' language has been deleted';
        $message_type = 'alert-success';
        try {
            Filesystem::delete($language_file);
        } catch (Exception $e) {
            $message = $e->getMessage();
            $message_type = 'alert-danger';
        }
    }
}

// Handle "Activate" language if requested
else if (!empty($_GET['activate']) && !ctype_space($_GET['activate'])) {

    // Validate theme
    $language_file = DOC_ROOT . '/cc-content/languages/' . $_GET['activate'] . '.xml';
    if (file_exists($language_file)) {
        $xml = simplexml_load_file($language_file);
        $active_languages[] = (object) array (
            'system_name' => $_GET['activate'],
            'lang_name' => (string) $xml->information->lang_name,
            'native_name' => (string) $xml->information->native_name
        );
        $languageSystemNames[] = $_GET['activate'];
        Settings::set('active_languages', json_encode($active_languages));
        $message = $xml->information->lang_name . ' has been activated.';
        $message_type = 'alert-success';
    }
}

// Handle "Deactivate" language if requested
else if (!empty($_GET['deactivate']) && !ctype_space($_GET['deactivate'])) {

    // Validate theme
    $language_file = DOC_ROOT . '/cc-content/languages/' . $_GET['deactivate'] . '.xml';
    $key = array_search($_GET['deactivate'], $languageSystemNames);
    if ($key !== false) {
        $xml = simplexml_load_file($language_file);
        unset($active_languages[$key]);
        Settings::set('active_languages', json_encode(array_values($active_languages)));
        $message = $xml->information->lang_name . ' has been deactivated.';
        $message_type = 'alert-success';
    }
}

// Handle "Set Default" language if requested
else if (!empty($_GET['default']) && !ctype_space($_GET['default'])) {

    // Validate language
    $language_file = DOC_ROOT . '/cc-content/languages/' . $_GET['default'] . '.xml';
    $key = array_search($_GET['default'], $languageSystemNames);
    if ($key !== false) {
        $xml = simplexml_load_file($language_file);
        Settings::set('default_language', $_GET['default']);
        $message = $xml->information->lang_name . ' is now the default language.';
        $message_type = 'alert-success';
    }
}

// Retrieve languages
foreach (glob(DOC_ROOT . '/cc-content/languages/*') as $language) {
    $lang = new stdClass();
    $lang->filename = basename($language, '.xml');
    $lang->active = (in_array($lang->filename, $languageSystemNames)) ? true : false;
    $lang->default = ($lang->filename == Settings::get('default_language')) ? true : false;
    $lang->xml = simplexml_load_file($language);
    $lang_list[] = $lang;
}

// Output Header
$pageName = 'languages';
include ('header.php');

?>

<link rel="stylesheet" type="text/css" href="<?=ADMIN?>/extras/fancybox/jquery.fancybox-1.3.4.css" />

<h1>Languages</h1>

<?php if ($message): ?>
<div class="alert <?=$message_type?>"><?=$message?></div>
<?php endif; ?>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Language</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        

<?php foreach ($lang_list as $language): ?>

    <tr>
        <td>
            <p class="h3"><?=$language->xml->information->lang_name?></p>
            <p>
                <?php if ($language->active && $language->default): ?>
                    <strong>Default Language</strong>
                <?php else: ?>

                    <?php if ($language->active): ?>
                        <a href="<?=ADMIN?>/languages.php?default=<?=$language->filename?>">Set Default</a> &nbsp;&nbsp;|&nbsp;&nbsp;
                        <a href="<?=ADMIN?>/languages.php?deactivate=<?=$language->filename?>">Deactivate</a>
                    <?php else: ?>
                        <a href="<?=ADMIN?>/languages.php?activate=<?=$language->filename?>">Activate</a>
                    <?php endif; ?>

                    &nbsp;&nbsp;|&nbsp;&nbsp; <a href="<?=HOST?>/?preview_lang=<?=$language->filename?>" class="iframe">Preview</a>
                    &nbsp;&nbsp;|&nbsp;&nbsp; <a href="<?=ADMIN?>/languages.php?delete=<?=$language->filename?>" class="delete confirm" data-confirm="You're about to delete this language file. This cannot be undone. Do you want to proceed?">Delete</a>
                <?php endif; ?>
            </p>
        </td>
        <td>
            <p><strong>Sample:</strong> <?=$language->xml->information->sample?></p>
            <?php if (!empty($language->xml->information->author)): ?>
                <p>By: <?=$language->xml->information->author?></p>
            <?php endif; ?>

            <?php if (!empty($language->xml->information->notes)): ?>
                <p><?=$language->xml->information->notes?></p>
            <?php endif; ?>
        </td>
    </tr>

<?php endforeach; ?>
    </tbody>
</table>

<?php include('footer.php'); ?>