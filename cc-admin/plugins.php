<?php

### Created on February 28, 2009
### Created by Miguel A. Hurtado
### This script displays the site homepage


// Include required files
include ('../cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Filesystem');


// Establish page variables, objects, arrays, etc
Plugin::Trigger ('admin.videos.start');
//$logged_in = User::LoginCheck(HOST . '/login/');
//$admin = new User ($logged_in);
$message = null;
$page_title = 'Plugins';
$plugin_list = array();
$active_plugins = Plugin::GetActivePlugins();




### Handle "Delete" plugin if requested
if (!empty ($_GET['delete']) && !ctype_space ($_GET['delete'])) {

    if (Plugin::ValidPlugin ($_GET['delete'])) {

        // Uninstall plugin if applicable
        $key = array_search ($_GET['delete'], $active_plugins);
        if ($key !== false) {
            unset ($active_plugins[$key]);
            Settings::Set ('active_plugins', serialize ($active_plugins));
            if (method_exists ($_GET['delete'], 'Uninstall')) call_user_func (array ($_GET['delete'], 'Uninstall'));
        }
        
        // Delete plugin files
        $plugin_info = Plugin::GetPluginInfo ($_GET['delete']);
        $message = $plugin_info->plugin_name . ' plugin has been deleted';
        $message_type = 'success';
        try {
            Filesystem::Open();
            Filesystem::Delete (DOC_ROOT . '/cc-content/plugins/' . $_GET['delete']);
            Filesystem::Close();
        } catch (Exception $e) {
            $message = $e->getMessage();
            $message_type = 'error';
        }

    }

}




### Handle "Activate" plugin if requested
else if (!empty ($_GET['activate']) && !ctype_space ($_GET['activate'])) {

    // Validate plugin
    if (Plugin::ValidPlugin ($_GET['activate'])) {
        if (method_exists ($_GET['activate'], 'Install')) call_user_func (array ($_GET['activate'], 'Install'));
        $active_plugins[] = $_GET['activate'];
        Settings::Set ('active_plugins', serialize ($active_plugins));

        // Output message
        $plugin_info = Plugin::GetPluginInfo ($_GET['activate']);
        $message = $plugin_info->plugin_name . ' has been activated.';
        $message_type = 'success';
    }

}




### Handle "Deactivate" plugin if requested
else if (!empty ($_GET['deactivate']) && !ctype_space ($_GET['deactivate'])) {

//    echo Plugin::ValidPlugin ($_GET['deactivate']) ? 'yes' : 'no';
//    exit();

    // Uninstall plugin if applicable
    $key = array_search ($_GET['deactivate'], $active_plugins);
    if ($key !== false && Plugin::ValidPlugin ($_GET['deactivate'])) {
        unset ($active_plugins[$key]);
        Settings::Set ('active_plugins', serialize ($active_plugins));
        if (method_exists ($_GET['deactivate'], 'Uninstall')) call_user_func (array ($_GET['deactivate'], 'Uninstall'));

        // Output message
        $plugin_info = Plugin::GetPluginInfo ($_GET['deactivate']);
        $message = $plugin_info->plugin_name . ' has been deactivated.';
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
    $plugin->active = (in_array ($plugin->filename, $active_plugins)) ? true : false;
    $plugin->settings = (method_exists ($plugin_name, 'settings')) ? true : false;
    $plugin_list[] = $plugin;
    
}


// Output Header
include ('header.php');

?>

<div id="plugins">

    <h1>Plugins</h1>

    <?php if ($message): ?>
    <div class="<?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <?php foreach ($plugin_list as $plugin): ?>
    
        <div class="block">

            <p>
                <strong><?=$plugin->info->plugin_name?></strong>
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
                <?php if ($plugin->active && $plugin->settings): ?>
                    <a href="<?=ADMIN?>/settings_plugin.php?plugin=<?=$plugin->filename?>">Settings</a>
                <?php endif; ?>

                <?php if ($plugin->active): ?>
                    &nbsp;&nbsp;|&nbsp;&nbsp; <a href="<?=ADMIN?>/plugins.php?deactivate=<?=$plugin->filename?>">Deactivate</a>
                <?php else: ?>
                    <a href="<?=ADMIN?>/plugins.php?activate=<?=$plugin->filename?>">Activate</a>
                <?php endif; ?>

                &nbsp;&nbsp;|&nbsp;&nbsp; <a href="<?=ADMIN?>/plugins.php?delete=<?=$plugin->filename?>">Delete</a>
            </p>

        </div>
    
    <?php endforeach; ?>

</div>

<?php include ('footer.php'); ?>