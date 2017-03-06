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
$page_title = 'Edit Language';
$lang_list = array();
$missing = array();
$languageList = array();
$availableLanguages = array();
$installedLanguages = Language::getInstalled();
$defaultLanguage = Settings::get('default_language');
$queryString = array();
$subHeader = null;
$search = null;
$message = null;
$messageType = null;

// Validate language to edit
if (
    !empty($_GET['language'])
    && isset($installedLanguages->{$_GET['language']})
) {
    $systemName = $_GET['language'];
    $language = $installedLanguages->{$systemName};
    $queryString['language'] = $systemName;
} else {
    header("Location: " . ADMIN . '/languages.php');
    exit();
}

// Load system entries
$entries = Language::loadEntries(
    DOC_ROOT . '/cc-content/languages/' . $systemName . '.xml'
);

// Load desktop theme entries
$desktopThemeEntries = Language::loadEntries(
    THEMES_DIR . '/' . Settings::get('active_theme') . '/languages/' . $systemName . '.xml'
);
$entries = array_replace_recursive($entries, $desktopThemeEntries);

// Load mobile theme entries
$mobileThemeEntries = Language::loadEntries(
    THEMES_DIR . '/' . Settings::get('active_mobile_theme') . '/languages/' . $systemName . '.xml'
);
$entries = array_replace_recursive($entries, $mobileThemeEntries);

// Load entries for enabled plugins
$enabledPlugins = Plugin::getEnabledPlugins();
foreach ($enabledPlugins as $pluginName) {
    $pluginEntries = Language::loadEntries(
        DOC_ROOT . '/cc-content/plugins/' . $pluginName . '/languages/' . $systemName . '.xml'
    );
    $entries = array_replace_recursive($entries, $pluginEntries);
}

// Clone entries to keep track of originals
$originals = $entries;

// Retrieve custom language entries
$textService = new TextService();
$customEntries = $textService->getLanguageEntries($systemName);

// Parse language entries for final text values
foreach ($customEntries as $entry) {
    $entries['terms'][$entry->name] = $entry->content;
}

// Add terms into pseudo entry DB
$textDb = array();
foreach ($entries['terms'] as $key => $text) {
    $textDb[] = (object) array(
        'key' => $key,
        'text' => $text,
        'original' => $originals['terms'][$key]
    );
}

// Handle Search Member Form
if (isset($_POST['search_submitted']) && !empty($_POST['search'])) {
    $search = strtolower(trim($_POST['search']));
    $queryString['search'] = $search;
    $subHeader = "Search Results for: <em>$search</em>";
} else if (!empty($_GET['search'])) {
    $search = strtolower(trim($_GET['search']));
    $queryString['search'] = $search;
    $subHeader = "Search Results for: <em>$search</em>";
}

// Perform search if submitted
if ($search) {
    $textDb = array_filter($textDb, function($entry) use ($search) {
        if (
            strpos(strtolower($entry->key), $search) !== false
            || strpos(strtolower($entry->text), $search) !== false
        ) {
            return true;
        } else {
            return false;
        }
    });
}

// Initialize pagination
$total = count($textDb);
$recordsPerPage = 40;
$url = ADMIN . '/languages_edit.php?' . http_build_query($queryString);
$pagination = new Pagination($url, $total, $recordsPerPage, false);
$startRecord = $pagination->getStartRecord();

// Retrieve records from pseudo DB
$records = array_slice($textDb, $startRecord, $recordsPerPage);

// Output Header
$pageName = 'languages-edit';
include ('header.php');

?>

<h1>Edit Language - <?=$language->lang_name?></h1>
<?php if ($subHeader): ?>
<h3><?=$subHeader?></h3>
<?php endif; ?>

<div class="alert <?=$messageType?>"><?=$message?></div>

<p><a href="<?=ADMIN?>/languages.php">Return to previous screen</a></p>

<div class="filters">
    <div class="search">
        <form method="POST" action="<?=ADMIN?>/languages_edit.php?language=<?=$systemName?>">
            <input type="hidden" name="search_submitted" value="true" />
            <input class="form-control" type="text" name="search" value="<?=$search?>" />
            <input type="submit" name="submit" class="button" value="Search" />
        </form>
    </div>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Entry Name</th>
            <th>Text</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($records as $index => $entry): ?>
        <tr>
            <td class="col-xs-3"><?=$entry->key?></td>
            <td class="col-xs-9" data-language="<?=$systemName?>" data-key="<?=$entry->key?>" data-original="<?=htmlspecialchars($entry->original)?>">
                <div class="entry">
                    <span class="text"><?=htmlspecialchars(Functions::cutOff($entry->text, 70))?></span>
                    <a href="" class="pull-right delete reset">Reset</a>
                    <a href="" class="pull-right edit">Edit</a>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include('footer.php'); ?>