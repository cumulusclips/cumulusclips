<?php

// Include Required Files
include ('config.php');

// Load Main Classes
if (!class_exists('App')) include (LIB . '/App.php');
App::MaintCheck();
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
$theme = Functions::CurrentTheme();
define ('THEME', HOST . '/cc-content/themes/' . $theme);
define ('THEME_PATH', THEMES_DIR . '/' . $theme);


// Start session
if (!headers_sent() && session_id() == '') {
    session_start();
}

Plugin::Trigger ('app.start');

?>
