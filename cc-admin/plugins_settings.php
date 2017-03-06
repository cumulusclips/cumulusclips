<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$authService->enforceTimeout(true);

// Verify user can access admin panel
$userService = new \UserService();
Functions::RedirectIf($userService->checkPermissions('manage_settings', $adminUser), HOST . '/account/');

// Validate plugin
if (empty($_GET['plugin']) || !Plugin::isPluginValid($_GET['plugin'])) {
    App::Throw404();
}

$plugin = Plugin::getPlugin($_GET['plugin']);
$page_title = $plugin->name . ' Settings';
$pageName = 'plugins-settings';
$admin_meta['plugin'] = $plugin->getSystemName();
$admin_meta['pluginUrl'] = HOST . '/cc-content/plugins/' . $plugin->getSystemName();

// Verify plugin is enabled and has 'Settings'
if (!Plugin::hasSettingsMethod($plugin)) {
    App::Throw404();
}

// Execute plugin's settings method
ob_start();
$plugin->settings();
$body = ob_get_contents();
ob_end_clean();

// Output Page
include('header.php');

?>

<?php echo $body; ?>

<?php include('footer.php'); ?>