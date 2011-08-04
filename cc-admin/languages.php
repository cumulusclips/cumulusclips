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
$page_title = 'Languages';
$lang_list = array();
$admin_js[] = ADMIN . '/extras/fancybox/jquery.fancybox-1.3.4.js';
$admin_js[] = ADMIN . '/js/fancybox.js';
$active_languages = unserialize (Settings::Get ('active_languages'));


### Handle "Delete" theme if requested
if (!empty ($_GET['delete']) && !ctype_space ($_GET['delete']) && Functions::ValidTheme ($_GET['delete'])) {

    $theme_path = THEMES_DIR . '/' . $_GET['delete'];
    $xml = simplexml_load_file ($theme_path . '/theme.xml');
    if (Settings::Get('active_theme') != $_GET['delete']) {

        // DELETE THEME CODE
        $message = $xml->name . ' theme has been deleted';
        $message_type = 'success';
        try {
            Filesystem::Open();
            Filesystem::Delete ($theme_path);
            Filesystem::Close();
        } catch (Exception $e) {
            $message = $e->getMessage();
            $message_type = 'error';
        }

    } else {
        $message = 'Active theme cannot be deleted. Activate another theme and then try again';
        $message_type = 'error';
    }

}


### Handle "Activate" theme if requested
else if (!empty ($_GET['activate']) && !ctype_space ($_GET['activate'])) {

    // Validate theme
    if (Functions::ValidTheme ($_GET['activate'])) {
        $xml = simplexml_load_file (THEMES_DIR . '/' . $_GET['activate'] . '/theme.xml');
        Settings::Set ('active_theme', $_GET['activate']);
        $message = $xml->name . ' is now the active theme';
        $message_type = 'success';
    }

}




// Retrieve languages
foreach (glob (DOC_ROOT . '/cc-content/languages/*') as $language) {
    $lang = new stdClass();
    $lang->filename = basename ($language, '.xml');
    $lang->active = (in_array ($lang->filename, $active_languages)) ? true : false;
    $lang->default = ($lang->filename == Settings::Get ('default_language')) ? true : false;
    $lang->xml = simplexml_load_file ($language);
    $lang_list[] = $lang;
}
//echo '<pre>',print_r ($lang_list,true),'</pre>';
//exit();


// Output Header
include ('header.php');

?>

<link rel="stylesheet" type="text/css" href="<?=ADMIN?>/extras/fancybox/jquery.fancybox-1.3.4.css" />
<div id="themes">

    <h1>Languages</h1>

    <?php if ($message): ?>
    <div class="<?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <?php foreach ($lang_list as $language): ?>
    
        <div class="block">

            <p>
                <strong><?=$language->xml->information->lang_name?></strong>
                <?php if (!empty ($language->xml->information->author)): ?>
                    by: <?=$language->xml->information->author?>
                <?php endif; ?>
            </p>

            <p><strong>Sample:</strong> <?=$language->xml->information->sample?></p>
            
            <?php if (!empty ($language->xml->information->notes)): ?>
                <p><?=$language->xml->information->notes?></p>
            <?php endif; ?>


            <p>
                <?php if ($language->default): ?>
                    <strong>Default Language</strong>
                <?php else: ?>

                    <?php if ($language->active): ?>
                        <a href="<?=ADMIN?>/laguage.php?default=<?=$language->filename?>">Set Default</a> &nbsp;&nbsp;|&nbsp;&nbsp;
                        <a href="<?=ADMIN?>/laguage.php?deactivate=<?=$language->filename?>">Deactivate</a>
                    <?php else: ?>
                        <a href="<?=ADMIN?>/laguage.php?activate=<?=$language->filename?>">Activate</a>
                    <?php endif; ?>

                    &nbsp;&nbsp;|&nbsp;&nbsp; <a href="<?=HOST?>/?preview_lang=<?=$language->filename?>" class="iframe">Preview</a>
                    &nbsp;&nbsp;|&nbsp;&nbsp; <a href="<?=ADMIN?>/laguage.php?delete=<?=$language->filename?>">Delete</a>
                <?php endif; ?>
            </p>

        </div>
    
    <?php endforeach; ?>

</div>

<?php include ('footer.php'); ?>