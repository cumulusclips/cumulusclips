<?php

// Define system paths and vars
define('DOC_ROOT', dirname(dirname(dirname(__FILE__))));
define('THEMES_DIR', DOC_ROOT . '/cc-content/themes');
define('LOG', DOC_ROOT . '/cc-core/logs');
define('CONVERSION_LOG', LOG . '/converter.log');
define('DATABASE_LOG', LOG . '/database.log');
define('UPLOAD_PATH', DOC_ROOT . '/cc-content/uploads');
define('CURRENT_VERSION', '1.3.2');
define('LOG_QUERIES', true);
define('DATE_FORMAT', 'Y-m-d H:i:s');
define('MOTHERSHIP_URL', 'http://mothership.cumulusclips.org');
date_default_timezone_set('America/New_York');

// Load App class and perform pre-init checks
if (!class_exists('App')) include(DOC_ROOT . '/cc-core/lib/App.php');

// Set Include Path
$includePath = get_include_path();
$includePath .= PATH_SEPARATOR . DOC_ROOT . '/cc-core/lib';
$includePath .= PATH_SEPARATOR . DOC_ROOT . '/cc-core/models';
$includePath .= PATH_SEPARATOR . DOC_ROOT . '/cc-core/mappers';
$includePath .= PATH_SEPARATOR . DOC_ROOT . '/cc-core/services';
if (!set_include_path($includePath)) exit('CC-ERROR-100 Host does not allow custom include paths.');
spl_autoload_register(array('App', 'LoadClass'));

// Checks
App::InstallCheck();
App::MaintCheck();

// Load DB & FTP credentials
include_once('config.php');

// Retrieve site settings from DB
$db = new Database();
Registry::set('db', $db);
Settings::LoadSettings();

// General Site Settings from DB
define('HOST', Settings::Get('base_url'));
define('MOBILE_HOST', Settings::Get('base_url') . '/m');
define('SECRET_KEY', Settings::Get('secret_key'));

$config = new stdClass();
$config->sitename = Settings::Get('sitename');
$config->roles = unserialize(Settings::Get('roles'));
$config->enable_uploads = Settings::Get('enable_uploads');
$config->debug_conversion = Settings::Get('debug_conversion') == '1' ? true : false;
$config->video_size_limit = Settings::Get('video_size_limit');
$config->accepted_video_formats = array('flv', 'wmv', 'avi', 'ogg', 'mpg', 'mp4', 'mov', 'm4v');
$config->accepted_avatar_formats = array('png', 'jpeg', 'jpg', 'gif');
$config->pagination_page_limit = Settings::Get('pagination_page_limit');
$config->theme_default = 'cumulus';
$config->theme_url_default = HOST . '/cc-content/themes/cumulus';
$config->theme_path_default = THEMES_DIR . '/cumulus';

$config->h264Url = HOST . '/cc-content/uploads/h264';
$config->theoraUrl = HOST . '/cc-content/uploads/theora';
$config->vp8Url = HOST . '/cc-content/uploads/vp8';
$config->mobile_url = HOST . '/cc-content/uploads/mobile';
$config->thumb_url = HOST . '/cc-content/uploads/thumbs';

// Start session
if (!headers_sent() && session_id() == '') @session_start();

// Initialize plugin system
Plugin::init();
Plugin::triggerEvent('app.start');

// Define Theme settings
$theme = App::CurrentTheme();
$theme = Plugin::triggerFilter('app.before_set_theme', $theme);
define('THEME', HOST . '/cc-content/themes/' . $theme);
define('THEME_PATH', THEMES_DIR . '/' . $theme);
$config->theme_url = HOST . '/cc-content/themes/' . $theme;
$config->theme_path = THEMES_DIR . '/' . $theme;
Registry::set('config', $config);

// Load language
Language::LoadLangPack(App::CurrentLang());

// Check for mobile devices
App::MobileCheck();