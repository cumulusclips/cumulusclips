<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/config/admin.bootstrap.php');

// Verify if user is logged in
$userService = new UserService();
$adminUser = $userService->loginCheck();
Functions::RedirectIf($adminUser, HOST . '/login/');
Functions::RedirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/myaccount/');

// Establish page variables, objects, arrays, etc
$message = null;
$page_title = 'Plugins';
$plugin_list = array();
$installed_plugins = unserialize (Settings::Get ('installed_plugins'));
$enabled_plugins = Plugin::GetEnabledPlugins();




### Handle "Delete" plugin if requested
if (!empty ($_GET['delete']) && !ctype_space ($_GET['delete'])) {

    if (Plugin::ValidPlugin ($_GET['delete'])) {

        // Disable plugin if applicable
        $key = array_search ($_GET['delete'], $enabled_plugins);
        if ($key !== false) {
            unset ($enabled_plugins[$key]);
            Settings::Set ('enabled_plugins', serialize ($enabled_plugins));
        }

        // Uninstall plugin
        $key = array_search ($_GET['delete'], $installed_plugins);
        if ($key !== false) {
            if (method_exists ($_GET['delete'], 'Uninstall')) call_user_func (array ($_GET['delete'], 'Uninstall'));
            unset ($installed_plugins[$key]);
            Settings::Set ('installed_plugins', serialize ($installed_plugins));
        }

        // Delete plugin files
        $plugin_info = Plugin::GetPluginInfo ($_GET['delete']);
        $message = $plugin_info->name . ' plugin has been deleted';
        $message_type = 'success';
        try {
            Filesystem::delete(DOC_ROOT . '/cc-content/plugins/' . $_GET['delete']);
        } catch (Exception $e) {
            $message = $e->getMessage();
            $message_type = 'errors';
        }

    }

}




### Handle "Enable" plugin if requested
else if (!empty ($_GET['enable']) && !ctype_space ($_GET['enable'])) {

    // Validate plugin
    if (Plugin::ValidPlugin ($_GET['enable']) && !in_array ($_GET['enable'], $enabled_plugins)) {

        // Install plugin if applicable
        if (!in_array ($_GET['enable'], $installed_plugins)) {
            if (method_exists ($_GET['enable'], 'Install')) call_user_func (array ($_GET['enable'], 'Install'));
            $installed_plugins[] = $_GET['enable'];
            Settings::Set ('installed_plugins', serialize ($installed_plugins));
        }

        // Enable plugin
        $enabled_plugins[] = $_GET['enable'];
        Settings::Set ('enabled_plugins', serialize ($enabled_plugins));

        // Output message
        $plugin_info = Plugin::GetPluginInfo ($_GET['enable']);
        $message = $plugin_info->name . ' has been enabled.';
        $message_type = 'success';
    }

}




### Handle "Disable" plugin if requested
else if (!empty ($_GET['disable']) && !ctype_space ($_GET['disable'])) {

    // Uninstall plugin if applicable
    $key = array_search ($_GET['disable'], $enabled_plugins);
    if ($key !== false && Plugin::ValidPlugin ($_GET['disable'])) {

        unset ($enabled_plugins[$key]);
        Settings::Set ('enabled_plugins', serialize ($enabled_plugins));

        // Output message
        $plugin_info = Plugin::GetPluginInfo ($_GET['disable']);
        $message = $plugin_info->name . ' has been disabled.';
        $message_type = 'success';

    }

}




// Retrieve plugins
foreach (glob (DOC_ROOT . '/cc-content/plugins/*') as $plugin_path) {

    // Load plugin and retrieve it's info
    $plugin_name = basename ($plugin_path);
    include_once ("$plugin_path/$plugin_name.php");

    // Store info for output
    $plugin = new stdClass();
    $plugin->filename = $plugin_name;
    $plugin->info = Plugin::GetPluginInfo ($plugin_name);
    $plugin->enabled = (in_array ($plugin->filename, $enabled_plugins)) ? true : false;
    $plugin->settings = (method_exists ($plugin_name, 'settings')) ? true : false;
    $plugin_list[] = $plugin;
    
}


// Output Header
include ('header.php');

?>

<div id="plugins">

    <h1>Plugins</h1>

    <?php if ($message): ?>
    <div class="message <?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <?php if (!empty ($plugin_list)): ?>

        <?php foreach ($plugin_list as $plugin): ?>

            <div class="block">

                <p>
                    <strong><?=$plugin->info->name?></strong>
                    <?php if (!empty ($plugin->info->author)): ?>
                        by: <?=$plugin->info->author?>
                    <?php endif; ?>
                </p>


                <?php if (!empty ($plugin->info->version)): ?>
                <p><strong>Version:</strong> <?=$plugin->info->version?></p>
                <?php endif; ?>


                <?php if (!empty ($plugin->info->notes)): ?>
                    <p><?=$plugin->info->notes?></p>
                <?php endif; ?>


                <p>
                    <?php if ($plugin->enabled && $plugin->settings): ?>
                        <a href="<?=ADMIN?>/plugins_settings.php?plugin=<?=$plugin->filename?>">Settings</a> &nbsp;&nbsp;|&nbsp;&nbsp;
                    <?php endif; ?>

                    <?php if ($plugin->enabled): ?>
                        <a href="<?=ADMIN?>/plugins.php?disable=<?=$plugin->filename?>">Disable</a>
                    <?php else: ?>
                        <a href="<?=ADMIN?>/plugins.php?enable=<?=$plugin->filename?>">Enable</a>
                    <?php endif; ?>

                    &nbsp;&nbsp;|&nbsp;&nbsp; <a href="<?=ADMIN?>/plugins.php?delete=<?=$plugin->filename?>" class="delete confirm" data-confirm="This will completely uninstall and remove this plugin from your system. Do you want to proceed?">Uninstall &amp; Delete</a>
                </p>

            </div>

        <?php endforeach; ?>

    <?php else: ?>
        <div class="block"><strong>No plugins added yet.</strong></div>
    <?php endif; ?>

</div>

<?php include ('footer.php'); ?>