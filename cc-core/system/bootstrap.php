<?php

// Define system paths and vars
define('DOC_ROOT', dirname(dirname(dirname(__FILE__))));
define('THEMES_DIR', DOC_ROOT . '/cc-content/themes');
define('LOG', DOC_ROOT . '/cc-core/logs');
define('CONVERSION_LOG', LOG . '/converter.log');
define('DATABASE_LOG', LOG . '/database.log');
define('UPLOAD_PATH', DOC_ROOT . '/cc-content/uploads');
define('CURRENT_VERSION', '2.5.3');
define('LOG_QUERIES', false);
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
if (!set_include_path($includePath)) exit('CC-ERROR-100 CumulusClips has encountered an error and cannot continue.');
spl_autoload_register(array('App', 'loadClass'));

// Checks
App::installCheck();
App::maintCheck();

// Load DB & FTP credentials
include_once('config.php');

// Retrieve site settings from DB
$db = new Database();
Registry::set('db', $db);
Settings::loadSettings();

// General Site Settings from DB
define('BASE_URL', Settings::get('base_url'));
define('MOBILE_BASE_URL', Settings::get('base_url') . '/m');
define('SECRET_KEY', Settings::get('secret_key'));

// @deprecated Deprecated in 2.5.0, removed in 2.6.0. Use BASE_URL instead
define('HOST', Settings::get('base_url'));

// @deprecated Deprecated in 2.5.0, removed in 2.6.0. Use MOBILE_BASE_URL instead
define('MOBILE_HOST', Settings::get('base_url') . '/m');

$config = new stdClass();
$config->baseUrl = BASE_URL;
$config->sitename = Settings::get('sitename');
$config->roles = json_decode(Settings::get('roles'));
$config->sessionTimeout = (int) Settings::get('session_timeout');
$config->enableUploads = Settings::get('enable_uploads');
$config->debugConversion = (boolean) Settings::get('debug_conversion');
$config->videoSizeLimit = Settings::get('video_size_limit');
$config->fileSizeLimit = Settings::get('file_size_limit');
$config->acceptedVideoFormats = array('flv', 'wmv', 'avi', 'ogg', 'mpg', 'mp4', 'mov', 'm4v', '3gp');
$config->acceptedImageFormats = array('png', 'jpeg', 'jpg', 'gif');
$config->h264Url = BASE_URL . '/cc-content/uploads/h264';
$config->theoraUrl = BASE_URL . '/cc-content/uploads/theora';
$config->webmUrl = BASE_URL . '/cc-content/uploads/webm';
$config->mobileUrl = BASE_URL . '/cc-content/uploads/mobile';
$config->thumbUrl = BASE_URL . '/cc-content/uploads/thumbs';
$config->enableRegistrations = (boolean) Settings::get('user_registrations');
$config->enableUserUploads = (boolean) Settings::get('user_uploads');
$config->allowVideoAttachments = (boolean) Settings::get('video_attachments');
$config->smtp = json_decode(Settings::get('smtp'));
$config->from_name = Settings::get('from_name');
$config->from_address = Settings::get('from_address');
Registry::set('config', $config);

// Session security ini values
ini_set('session.name', 'EID');
ini_set('session.use_strict_mode', true);
ini_set('session.cookie_httponly', true);
ini_set('session.use_cookies', true);
ini_set('session.use_only_cookies', true);
ini_set('session.use_trans_sid', true);
ini_set('session.cookie_domain', parse_url(BASE_URL, PHP_URL_HOST));
ini_set('session.cookie_path', (parse_url(BASE_URL, PHP_URL_PATH) ?: '') . '/');

// Initialize language
Language::init();

// Initialize plugin system
Plugin::init();
Plugin::triggerEvent('app.start');
