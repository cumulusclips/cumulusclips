<?php

### Created on February 28, 2009
### Created by Miguel A. Hurtado
### This script displays the site homepage


// Include required files
include ('../cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');
App::LoadClass ('Pagination');


// Establish page variables, objects, arrays, etc
Plugin::Trigger ('admin.videos.start');
//$logged_in = User::LoginCheck(HOST . '/login/');
//$admin = new User ($logged_in);
$message = null;
$page_title = 'Themes';
$header = 'Themes';
$admin_js[] = ADMIN . '/extras/fancybox/jquery.fancybox-1.3.4.js';
$admin_js[] = ADMIN . '/js/fancybox.js';


### Handle "Delete" theme if requested
if (!empty ($_GET['delete']) && !ctype_space ($_GET['delete'])) {

    // Validate theme
    if (file_exists (THEMES_DIR . '/' . $_GET['delete'] . '/theme.xml')) {

        $xml = simplexml_load_file (THEMES_DIR . '/' . $_GET['delete'] . '/theme.xml');
        if (Settings::Get('active_theme') != $_GET['delete']) {
            // DELETE THEME CODE
            $message = $xml->name . ' theme has been deleted';
            $message_type = 'success';
        } else {
            $message = 'Active theme cannot be deleted. Activate another theme and then try again';
            $message_type = 'error';
        }

    }

}


### Handle "Activate" theme if requested
else if (!empty ($_GET['activate']) && !ctype_space ($_GET['activate'])) {

    // Validate theme
    if (file_exists (THEMES_DIR . '/' . $_GET['activate'] . '/theme.xml')) {
        $xml = simplexml_load_file (THEMES_DIR . '/' . $_GET['activate'] . '/theme.xml');
        Settings::Set ('active_theme', $_GET['activate']);
        $message = $xml->name . ' is now the active theme';
        $message_type = 'success';
    }

}


// Output Header
include ('header.php');

?>

<link rel="stylesheet" type="text/css" href="<?=ADMIN?>/extras/fancybox/jquery.fancybox-1.3.4.css" />
<div id="themes">

    <h1><?=$header?></h1>


    <?php if ($message): ?>
    <div class="<?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <?php foreach (glob (THEMES_DIR . '/*') as $theme_path): ?>
    
        <div class="block theme">
            <?php
            $theme = basename ($theme_path);
            $theme_url = HOST . '/cc-content/themes/' . $theme;
            $xml = simplexml_load_file ($theme_path . '/theme.xml');
            ?>
            
            <?php if (file_exists ($theme_path . '/screenshot.png')): ?>
                <div class="screenshot"><img width="200" src="<?=$theme_url?>/screenshot.png" /></div>
            <?php endif; ?>

            <p>
                <strong><?=$xml->name?></strong>
                <?php if (!empty ($xml->author)): ?>
                    by: <strong><?=$xml->author?></strong>
                <?php endif; ?>
            </p>

            
            <?php if (!empty ($xml->description)): ?>
                <p><?=$xml->description?></p>
            <?php endif; ?>


            <p>
                <?php if ($theme == Settings::Get ('active_theme')): ?>
                    <strong>Active Theme</strong>
                <?php else: ?>
                    <a href="<?=ADMIN?>/themes.php?activate=<?=$theme?>">Activate</a>
                    &nbsp;&nbsp;|&nbsp;&nbsp; <a href="<?=HOST?>/?preview_theme=<?=$theme?>" class="iframe">Preview</a>
                    &nbsp;&nbsp;|&nbsp;&nbsp; <a href="<?=ADMIN?>/themes.php?delete=<?=$theme?>">Delete</a>
                <?php endif; ?>
            </p>

        </div>
    
    <?php endforeach; ?>

</div>

<?php include ('footer.php'); ?>