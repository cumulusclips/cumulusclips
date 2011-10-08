<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');


// Establish page variables, objects, arrays, etc
Plugin::Trigger ('admin.plugin_settings.start');
Functions::RedirectIf ($logged_in = User::LoginCheck(), HOST . '/login/');
$admin = new User ($logged_in);
Functions::RedirectIf (User::CheckPermissions ('admin_panel', $admin), HOST . '/myaccount/');
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