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
$page_title = 'Themes';
$admin_js[] = ADMIN . '/extras/fancybox/jquery.fancybox-1.3.4.js';
$admin_js[] = ADMIN . '/js/fancybox.js';


### Handle "Delete" theme if requested
if (!empty ($_GET['delete']) && !ctype_space ($_GET['delete']) && Functions::ValidTheme ($_GET['delete'])) {

    $theme_path = THEMES_DIR . '/' . $_GET['delete'];
    $xml = simplexml_load_file ($theme_path . '/theme.xml');
    if (Settings::Get('active_theme') != $_GET['delete']) {

        // DELETE THEME CODE
        $message = $xml->name . ' theme has been deleted';
        $message_type = 'success';
        try {
            Filesystem::delete($theme_path);
        } catch (Exception $e) {
            $message = $e->getMessage();
            $message_type = 'errors';
        }

    } else {
        $message = 'Active theme cannot be deleted. Activate another theme and then try again';
        $message_type = 'errors';
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



// Retrieve themes
foreach (glob (THEMES_DIR . '/*') as $theme_path) {
    
    $theme = new stdClass();
    $theme->name = basename ($theme_path);
    if (Functions::ValidTheme ($theme->name)) {
        $theme->url = HOST . '/cc-content/themes/' . $theme->name;
        $theme->xml = simplexml_load_file ($theme_path . '/theme.xml');

        // Add theme to corresponding theme type
        if ($theme->xml->mobile == 'true') {
            $mobile_themes[] = $theme;
        } else {
            $main_site_themes[] = $theme;
        }

    }
    
}




// Output Header
include ('header.php');

?>

<link rel="stylesheet" type="text/css" href="<?=ADMIN?>/extras/fancybox/jquery.fancybox-1.3.4.css" />
<div id="themes">

    <!-- BEGIN MAIN SITE THEMES -->
    <h1>Themes</h1>

    <?php if ($message): ?>
    <div class="message <?=$message_type?>"><?=$message?></div>
    <?php endif; ?>


    <?php foreach ($main_site_themes as $theme): ?>
    
        <div class="block theme">
            
            <div class="screenshot"><img width="200" src="<?=$theme->url?>/screenshot.png" /></div>

            <p>
                <strong><?=$theme->xml->name?></strong>
                <?php if (!empty ($theme->xml->author)): ?>
                    by: <strong><?=$theme->xml->author?></strong>
                <?php endif; ?>
            </p>

            
            <?php if (!empty ($theme->xml->description)): ?>
                <p><?=$theme->xml->description?></p>
            <?php endif; ?>


            <p>
                <?php if ($theme->name == Settings::Get ('active_theme')): ?>
                    <strong>Active Theme</strong>
                <?php else: ?>
                    <a href="<?=ADMIN?>/themes.php?activate=<?=$theme->name?>">Activate</a>
                    &nbsp;&nbsp;|&nbsp;&nbsp; <a href="<?=HOST?>/?preview_theme=<?=$theme->name?>" class="iframe">Preview</a>
                    &nbsp;&nbsp;|&nbsp;&nbsp; <a href="<?=ADMIN?>/themes.php?delete=<?=$theme->name?>" class="delete confirm" data-confirm="You're about to delete this theme and all of it's files. This cannot be undone. Do you want to proceed?">Delete</a>
                <?php endif; ?>
            </p>

        </div>
    
    <?php endforeach; ?>
    <!-- END MAIN SITE THEMES -->






    <!-- BEGIN MOBILE THEMES -->
    <h1>Mobile Themes</h1>
    <?php foreach ($mobile_themes as $theme): ?>

        <div class="block theme">

            <div class="screenshot"><img width="200" src="<?=$theme->url?>/screenshot.png" /></div>

            <p>
                <strong><?=$theme->xml->name?></strong>
                <?php if (!empty ($theme->xml->author)): ?>
                    by: <strong><?=$theme->xml->author?></strong>
                <?php endif; ?>
            </p>


            <?php if (!empty ($theme->xml->description)): ?>
                <p><?=$theme->xml->description?></p>
            <?php endif; ?>


            <p>
                <?php if ($theme->name == Settings::Get ('active_mobile_theme')): ?>
                    <strong>Active Theme</strong>
                <?php else: ?>
                    <a href="<?=ADMIN?>/themes.php?activate=<?=$theme->name?>">Activate</a>
                    &nbsp;&nbsp;|&nbsp;&nbsp; <a href="<?=HOST?>/?preview_theme=<?=$theme->name?>" class="iframe">Preview</a>
                    &nbsp;&nbsp;|&nbsp;&nbsp; <a href="<?=ADMIN?>/themes.php?delete=<?=$theme->name?>">Delete</a>
                <?php endif; ?>
            </p>

        </div>

    <?php endforeach; ?>
    <!-- END MOBILE THEMES -->

</div>

<?php include ('footer.php'); ?>