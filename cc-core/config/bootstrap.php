<?php

// Define system paths and vars
define ('DOC_ROOT', dirname (dirname ( dirname ( __FILE__ ))));
define ('LIB', DOC_ROOT . '/cc-core/lib');
define ('THEMES_DIR', DOC_ROOT . '/cc-content/themes');
define ('LOG', DOC_ROOT . '/cc-core/logs');
define ('CONVERSION_LOG', LOG . '/converter.log');
define ('QUERY_LOG', LOG . '/query.log');
define ('DB_ERR_LOG', LOG . '/db_errors.log');
define ('UPLOAD_PATH', DOC_ROOT . '/cc-content/uploads');
define ('EMAIL_PATH', DOC_ROOT . '/cc-content/emails');

define ('CURRENT_VERSION', '1.0');
define ('LOG_QUERIES', false);
define ('SITE_EMAIL', 'admin@techievideos.com');
define ('UPDATE_URL', 'http://updates.cumulusclips.org');
ini_set ('max_execution_time', 3600);


// Load App class and perform pre-init checks
if (!class_exists ('App')) include (LIB . '/App.php');
App::InstallCheck();
App::MaintCheck();


// Load DB & FTP credentials
include_once ('config.php');


// Load Main Classes
App::LoadClass ('Database');
App::LoadClass ('Settings');
App::LoadClass ('Functions');
App::LoadClass ('Language');
App::LoadClass ('View');
App::LoadClass ('Plugin');


// Retrieve site settings from DB
$db = Database::GetInstance();
Settings::LoadSettings();


// General Site Settings from DB
define ('HOST', Settings::Get ('base_url'));
define ('MOBILE_HOST', Settings::Get ('base_url') . '/m');
define ('SECRET_KEY', Settings::Get ('secret_key'));
define ('ADMIN_EMAIL', Settings::Get ('admin_email'));

$config = new stdClass();
$config->accepted_video_formats = unserialize (Settings::Get ('accepted_video_formats'));
$config->video_size_limit = Settings::Get ('video_size_limit');
$config->pagination_page_limit = Settings::Get ('pagination_page_limit');
$config->flv_bucket_url = Settings::Get ('flv_bucket_url');
$config->mp4_bucket_url = Settings::Get ('mp4_bucket_url');
$config->thumb_bucket_url = Settings::Get ('thumb_bucket_url');


// Load language
Language::LoadLangPack (App::CurrentLang());


// Define Theme settings
$theme = App::CurrentTheme();
define ('THEME', HOST . '/cc-content/themes/' . $theme);
define ('THEME_PATH', THEMES_DIR . '/' . $theme);


// Start session
if (!headers_sent() && session_id() == '') {
    @session_start();
}


// Initialize plugin system
Plugin::Init();
Plugin::Trigger ('app.start');


// Check for mobile devices
App::MobileCheck();

?>