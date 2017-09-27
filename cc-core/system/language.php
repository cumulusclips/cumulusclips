<?php

// Node requested from language file
if (isset($_GET['action']) && $_GET['action'] == 'get') {

    // Return requested string
    if (!empty ($_POST['node'])) {
        if (!empty ($_POST['replacements']) && is_array ($_POST['replacements'])) {
            $replacements = $_POST['replacements'];
        } else {
            $replacements = array();
        }
        $string = Language::GetText ($_POST['node'], $replacements);
        echo $string ? $string : '';
        exit();
    }

// Language change requested
} else if (isset($_GET['action']) && $_GET['action'] == 'set') {

    // Set language to user's request
    $activeLanguages = Language::getActiveLanguages();
    if (isset($activeLanguages[$_GET['language']])) {
        setcookie(
            'user_lang',
            $_GET['language'],
            0,
            (parse_url(BASE_URL, PHP_URL_PATH) ?: '') . '/',
            parse_url(BASE_URL, PHP_URL_HOST),
            false,
            true
        );
    }

    // Redirect user to previous page
    if (!empty ($_SERVER['HTTP_REFERER'])) {
        header ('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    } else {
        header ('Location: ' . HOST . '/');
        exit();
    }
} else {
    App::Throw404();
}