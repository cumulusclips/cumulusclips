<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify user can access admin panel
$userService = new \UserService();
Functions::RedirectIf($userService->checkPermissions('manage_settings', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$installedLanguages = Language::getInstalled();
$textMapper = new TextMapper();

// Validate given language
if (
    !empty($_POST['language'])
    && isset($installedLanguages->{$_POST['language']})
) {
    $systemName = $_POST['language'];
    $language = $installedLanguages->{$systemName};
} else {
    header("Location: " . ADMIN . '/languages.php');
    exit();
}

// Validate given text content
if (isset($_POST['text'])) {
    $text = trim($_POST['text']);
} else {
    header("Location: " . ADMIN . '/languages.php');
    exit();
}

// Validate given entry key name
if (!empty($_POST['action']) && in_array($_POST['action'], array('reset', 'edit'))) {
    $action = $_POST['action'];
} else {
    header("Location: " . ADMIN . '/languages.php');
    exit();
}

// Validate given entry key name
if (!empty($_POST['key'])) {
    $type = (strpos($_POST['key'], 'meta.') === 0) ? 'meta' : 'terms';
    $key = $_POST['key'];
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

// Verify original entry exists in XML
if (!isset($entries['terms'][$key])) {
    header("Location: " . ADMIN . '/languages.php');
    exit();
}

// Retrieve original content
$originalText = $entries['terms'][$key];

// Retrieve custom entry
$textEntry = $textMapper->getByCustom(array(
    'type' => TextMapper::TYPE_LANGUAGE,
    'language' => $systemName,
    'name' => $key
));

// Handle text reset if requested
if ($action === 'reset') {

    // Delete custom entry if it exists
    if ($textEntry) {
        $textMapper->delete($textEntry->textId);
    }

    // Output response
    $response = new ApiResponse();
    $response->result = true;
    $response->data = (object) array('key' => $key, 'text' => $text, 'original' => $originalText);
    exit($response);
}

// Detect if given text matches original XML text
if ($originalText === $text) {

    // Delete custom entry since saved text is not custom
    if ($textEntry) {
        $textMapper->delete($textEntry->textId);
    }

} else {

    // Create a new custom text entry
    if (!$textEntry) {
        $textEntry = new Text();
        $textEntry->name = $key;
        $textEntry->type = TextMapper::TYPE_LANGUAGE;
        $textEntry->language = $systemName;
    }

    // Save custom text entry
    $textEntry->content = $text;
    $textMapper->save($textEntry);
}

// Output response
$response = new ApiResponse();
$response->result = true;
$response->data = (object) array('key' => $key, 'text' => $text, 'original' => $originalText);
echo $response;
