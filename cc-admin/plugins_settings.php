<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/config/admin.bootstrap.php');

// Verify if user is logged in
$userService = new UserService();
$adminUser = $userService->loginCheck();
Functions::RedirectIf($adminUser, HOST . '/login/');
Functions::RedirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/myaccount/');

// Establish page variables, objects, arrays, etc
$enabled_plugins = Plugin::GetEnabledPlugins();


// Validate plugin
if (!empty ($_GET['plugin']) && Plugin::ValidPlugin ($_GET['plugin'], true)) {
    $plugin = trim ($_GET['plugin']);
} else {
    App::Throw404();
}


// Verify plugin is enabled and has 'Settings'
if (array_search ($plugin, $enabled_plugins) !== false && method_exists ($plugin, 'Settings')) {
    $plugin_info = Plugin::GetPluginInfo ($plugin);
    $page_title = $plugin_info->name . ' Settings';
} else {
    App::Throw404();
}


// Output Page
Plugin::Trigger ("admin.$plugin.before_render");
include ('header.php');
call_user_func (array ($plugin, 'Settings'));
Plugin::Trigger ("admin.$plugin.settings");
include ('footer.php');

?>