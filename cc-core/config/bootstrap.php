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
define ('CURRENT_VERSION', '1.0.3');
define ('LOG_QUERIES', false);
define ('MOTHERSHIP_URL', 'http://mothership.cumulusclips.org');
date_default_timezone_set ('America/New_York');


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

$config = new stdClass();
$config->sitename = Settings::Get ('sitename');
$config->roles = unserialize (Settings::Get ('roles'));
$config->enable_uploads = Settings::Get ('enable_uploads');
$config->debug_conversion = Settings::Get ('debug_conversion') == '1' ? true : false;
$config->video_size_limit = Settings::Get ('video_size_limit');
$config->accepted_video_formats = array ('flv', 'wmv', 'avi', 'ogg', 'mpg', 'mp4', 'mov', 'm4v');
$config->accepted_avatar_formats = array ('png', 'jpeg', 'jpg', 'gif');
$config->pagination_page_limit = Settings::Get ('pagination_page_limit');

$flv_url = Settings::Get('flv_url');
$mobile_url = Settings::Get('mobile_url');
$thumb_url = Settings::Get('thumb_url');
$config->flv_url = (empty ($flv_url)) ? HOST . '/cc-content/uploads/flv' : $flv_url;
$config->mobile_url = (empty ($mobile_url)) ? HOST . '/cc-content/uploads/mobile' : $mobile_url;
$config->thumb_url = (empty ($thumb_url)) ? HOST . '/cc-content/uploads/thumbs' : $thumb_url;


// Define Theme settings
$theme = App::CurrentTheme();
define ('THEME', HOST . '/cc-content/themes/' . $theme);
define ('THEME_PATH', THEMES_DIR . '/' . $theme);


// Start session
if (!headers_sent() && session_id() == '') {
    @session_start();
}


// Load language
Language::LoadLangPack (App::CurrentLang());


// Initialize plugin system
Plugin::Init();
Plugin::Trigger ('app.start');


// Check for mobile devices
App::MobileCheck();

?>