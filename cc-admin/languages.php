<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/cc-core/config/admin.bootstrap.php');
App::LoadClass ('User');


// Establish page variables, objects, arrays, etc
Functions::RedirectIf ($logged_in = User::LoginCheck(), HOST . '/login/');
$admin = new User ($logged_in);
Functions::RedirectIf (User::CheckPermissions ('admin_panel', $admin), HOST . '/myaccount/');
$message = null;
$page_title = 'Languages';
$lang_list = array();
$admin_js[] = ADMIN . '/extras/fancybox/jquery.fancybox-1.3.4.js';
$admin_js[] = ADMIN . '/js/fancybox.js';
$active_languages = Language::GetActiveLanguages();


// Remove any orphaned languages
foreach ($active_languages as $key => $language) {
    $language_file = DOC_ROOT . '/cc-content/languages/' . $key . '.xml';
    if (!file_exists ($language_file)) {
        unset ($active_languages[$key]);
    }
}
reset ($active_languages);
Settings::Set ('active_languages', serialize ($active_languages));



### Handle "Delete" language if requested
if (!empty ($_GET['delete']) && !ctype_space ($_GET['delete'])) {

    $language_file = DOC_ROOT . '/cc-content/languages/' . $_GET['delete'] . '.xml';
    if (file_exists ($language_file) && $_GET['delete'] != Settings::Get ('default_language')) {

        // Deactivate language if applicable
        if (array_key_exists ($_GET['delete'], $active_languages)) {
            unset ($active_languages[$_GET['delete']]);
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
            $message_type = 'errors';
        }

    }

}


### Handle "Activate" language if requested
else if (!empty ($_GET['activate']) && !ctype_space ($_GET['activate'])) {

    // Validate theme
    $language_file = DOC_ROOT . '/cc-content/languages/' . $_GET['activate'] . '.xml';
    if (file_exists ($language_file)) {
        $xml = simplexml_load_file ($language_file);
        $active_languages[$_GET['activate']] = array (
            'lang_name' => (string) $xml->information->lang_name,
            'native_name' => (string) $xml->information->native_name
        );
        Settings::Set ('active_languages', serialize ($active_languages));
        $message = $xml->information->lang_name . ' has been activated.';
        $message_type = 'success';
    }

}


### Handle "Deactivate" language if requested
else if (!empty ($_GET['deactivate']) && !ctype_space ($_GET['deactivate'])) {

    // Validate theme
    $language_file = DOC_ROOT . '/cc-content/languages/' . $_GET['deactivate'] . '.xml';
    if (array_key_exists ($_GET['deactivate'], $active_languages)) {
        $xml = simplexml_load_file ($language_file);
        unset ($active_languages[$_GET['deactivate']]);
        Settings::Set ('active_languages', serialize ($active_languages));
        $message = $xml->information->lang_name . ' has been deactivated.';
        $message_type = 'success';
    }

}


### Handle "Set Default" language if requested
else if (!empty ($_GET['default']) && !ctype_space ($_GET['default'])) {

    // Validate language
    $language_file = DOC_ROOT . '/cc-content/languages/' . $_GET['default'] . '.xml';
    if (array_key_exists ($_GET['default'], $active_languages) && file_exists ($language_file)) {
        $xml = simplexml_load_file ($language_file);
        Settings::Set ('default_language', $_GET['default']);
        $message = $xml->information->lang_name . ' is now the default language.';
        $message_type = 'success';
    }

}




// Retrieve languages
foreach (glob (DOC_ROOT . '/cc-content/languages/*') as $language) {
    $lang = new stdClass();
    $lang->filename = basename ($language, '.xml');
    $lang->active = (array_key_exists ($lang->filename, $active_languages)) ? true : false;
    $lang->default = ($lang->filename == Settings::Get ('default_language')) ? true : false;
    $lang->xml = simplexml_load_file ($language);
    $lang_list[] = $lang;
}


// Output Header
include ('header.php');

?>

<link rel="stylesheet" type="text/css" href="<?=ADMIN?>/extras/fancybox/jquery.fancybox-1.3.4.css" />
<div id="languages">

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
                <?php if ($language->active && $language->default): ?>
                    <strong>Default Language</strong>
                <?php else: ?>

                    <?php if ($language->active): ?>
                        <a href="<?=ADMIN?>/languages.php?default=<?=$language->filename?>">Set Default</a> &nbsp;&nbsp;|&nbsp;&nbsp;
                        <a href="<?=ADMIN?>/languages.php?deactivate=<?=$language->filename?>">Deactivate</a>
                    <?php else: ?>
                        <a href="<?=ADMIN?>/languages.php?activate=<?=$language->filename?>">Activate</a>
                    <?php endif; ?>

                    &nbsp;&nbsp;|&nbsp;&nbsp; <a href="<?=HOST?>/?preview_lang=<?=$language->filename?>" class="iframe">Preview</a>
                    &nbsp;&nbsp;|&nbsp;&nbsp; <a href="<?=ADMIN?>/languages.php?delete=<?=$language->filename?>" class="delete confirm" data-confirm="You're about to delete this language file. This cannot be undone. Do you want to proceed?">Delete</a>
                <?php endif; ?>
            </p>

        </div>
    
    <?php endforeach; ?>

</div>

<?php include ('footer.php'); ?>