<?php

// Include Required Files
include ('config.php');

// Load Main Classes
if (!class_exists('App')) include (LIB . '/App.php');
App::LoadClass ('Database');
App::LoadClass ('Settings');
App::LoadClass ('Functions');
App::LoadClass ('Language');
App::LoadClass ('View');
App::LoadClass ('Plugin');

// Retrieve site settings from DB
$db = Database::GetInstance();
Settings::LoadSettings();
Language::LoadLangPack ('english');
Plugin::Init();

// Define Theme settings
define ('ADMIN', HOST . '/cc-admin');
define ('THEME', HOST . '/cc-content/themes/admin');
define ('THEME_PATH', THEMES_DIR . '/admin');

// Pre-Output Work
if (!headers_sent()) {

    // Start session
    if (session_id() == '') {
        session_start();
    }

    // Create admin settings cookie
    if (empty ($_COOKIE['cc_admin_settings'])) {
        $cc_admin_settings = array(
            'dashboard'     => 0,
            'videos'        => 0,
            'members'       => 0,
            'comments'      => 0,
            'flags'         => 0,
            'pages'         => 0,
            'appearance'    => 0,
            'plugins'       => 0,
            'settings'      => 0
        );
        setcookie('cc_admin_settings', http_build_query($cc_admin_settings));
    }
    
}

Plugin::Trigger ('app.start');

?>