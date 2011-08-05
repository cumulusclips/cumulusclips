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
$active_plugins = unserialize (Settings::Get ('active_plugins'));


### Remove any orphaned plugins
//foreach ($active_plugins as $key => $plugin) {
//    $lang_file = DOC_ROOT . '/cc-content/languages/' . $language . '.xml';
//    if (!file_exists ($lang_file)) {
//        unset ($active_languages[$key]);
//    }
//}
//Settings::Set ('active_languages', serialize ($active_languages));
//reset ($active_languages);




### Handle "Delete" theme if requested
if (!empty ($_GET['delete']) && !ctype_space ($_GET['delete'])) {

    $language_file = DOC_ROOT . '/cc-content/languages/' . $_GET['delete'] . '.xml';
    if (file_exists ($language_file) && $_GET['delete'] != Settings::Get ('default_language')) {

        // Deactivate language if applicable
        $key = array_search ($_GET['delete'], $active_languages);
        if ($key !== false) {
            unset ($active_languages[$key]);
            Settings::Set ('active_languages', serialize ($active_languages));
        }
        
        // Delete language file
        $xml = simplexml_load_file ($language_file);
        $message = $xml->information->lang_name . ' language has been deleted';
        $message_type = 'success';
        try {
            Filesystem::Open();
            Filesystem::Delete ($language_file);
            Filesystem::Close();
        } catch (Exception $e) {
            $message = $e->getMessage();
            $message_type = 'error';
        }

    }

}


### Handle "Activate" language if requested
else if (!empty ($_GET['activate']) && !ctype_space ($_GET['activate'])) {

    // Validate theme
    $language_file = DOC_ROOT . '/cc-content/languages/' . $_GET['activate'] . '.xml';
    if (file_exists ($language_file)) {
        $xml = simplexml_load_file ($language_file);
        $active_languages[] = $_GET['activate'];
        Settings::Set ('active_languages', serialize ($active_languages));
        $message = $xml->information->lang_name . ' has been activated.';
        $message_type = 'success';
    }

}


### Handle "Deactivate" language if requested
else if (!empty ($_GET['deactivate']) && !ctype_space ($_GET['deactivate'])) {

    // Validate theme
    $language_file = DOC_ROOT . '/cc-content/languages/' . $_GET['deactivate'] . '.xml';
    $key = array_search ($_GET['deactivate'], $active_languages);
    if ($key !== false) {
        $xml = simplexml_load_file ($language_file);
        unset ($active_languages[$key]);
        Settings::Set ('active_languages', serialize ($active_languages));
        $message = $xml->information->lang_name . ' has been deactivated.';
        $message_type = 'success';
    }

}


### Handle "Set Default" language if requested
else if (!empty ($_GET['default']) && !ctype_space ($_GET['default'])) {

    // Validate language
    $language_file = DOC_ROOT . '/cc-content/languages/' . $_GET['default'] . '.xml';
    if (in_array ($_GET['default'], $active_languages) && file_exists ($language_file)) {
        $xml = simplexml_load_file ($language_file);
        Settings::Set ('default_language', $_GET['default']);
        $message = $xml->information->lang_name . ' is now the default language.';
        $message_type = 'success';
    }

}




// Retrieve languages
foreach (glob (DOC_ROOT . '/cc-content/plugins/*') as $plugin_path) {


    include_once ($plugin_path . '/plugin.php');
    $plugin_name = basename ($plugin_path);
    $info = call_user_func (array ($plugin_name, 'Info'));
    $plugin = new stdClass();
    $plugin->filename = $info['plugin_name'];
    $plugin->active = (in_array ($plugin->filename, $active_plugins)) ? true : false;
    $plugin->info = $info;
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
                <strong><?=$plugin->info['plugin_name']?></strong>
                <?php if (!empty ($plugin->info['author'])): ?>
                    by: <?=$plugin->info['author']?>
                <?php endif; ?>
            </p>

            <?php if (!empty ($plugin->info['notes'])): ?>
                <p><?=$plugin->info['notes']?></p>
            <?php endif; ?>


            <p>
                <?php if ($plugin->active): ?>
                    <a href="<?=ADMIN?>/plugins.php?deactivate=<?=$plugin->filename?>">Deactivate</a>
                <?php else: ?>
                    <a href="<?=ADMIN?>/plugins.php?activate=<?=$plugin->filename?>">Activate</a>
                <?php endif; ?>

                &nbsp;&nbsp;|&nbsp;&nbsp; <a href="<?=ADMIN?>/plugins.php?delete=<?=$plugin->filename?>">Delete</a>
            </p>

        </div>
    
    <?php endforeach; ?>

</div>

<?php include ('footer.php'); ?>