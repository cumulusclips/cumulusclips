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
Plugin::Init();

// Define Theme settings
define ('ADMIN', HOST . '/cc-admin');
define ('THEME', HOST . '/cc-content/themes/admin');
define ('THEME_PATH', THEMES_DIR . '/admin');

// Start session
if (!headers_sent() && session_id() == '') {
    session_start();
}

Plugin::Trigger ('app.start');

?>