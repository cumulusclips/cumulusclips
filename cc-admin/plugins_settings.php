<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/config/admin.bootstrap.php');

// Verify if user is logged in
$userService = new UserService();
$adminUser = $userService->loginCheck();
Functions::RedirectIf($adminUser, HOST . '/login/');
Functions::RedirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/account/');

// Validate plugin
if (empty($_GET['plugin']) || !Plugin::isPluginValid($_GET['plugin'])) {
    App::Throw404();
}

$plugin = Plugin::getPlugin($_GET['plugin']);
$page_title = $plugin->name . ' Settings';

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
echo $body;
include('footer.php');