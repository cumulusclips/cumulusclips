<?php

### Created on February 28, 2009
### Created by Miguel A. Hurtado
### This script contains the site config settings


define ('LIVE', false);
define ('DB_HOST', 'localhost');
define ('DB_USER', 'root');
define ('DB_PASS', 'Damian646');
define ('DB_NAME', 'cumulus');
define ('DB_PREFIX', '');
define ('SECRET_KEY', 'highly secretive key');
define ('HOST', 'http://cumulus');
define ('DOC_ROOT', dirname (dirname ( dirname ( __FILE__ ))));



define ('LIB', DOC_ROOT . '/cc-core/lib');
define ('THEMES_DIR', DOC_ROOT . '/cc-content/themes');
define ('SITE_EMAIL', 'admin@techievideos.com');
define ('MAIN_EMAIL', 'miguel@mahurtado.com');
define ('LOG', DOC_ROOT . '/cc-core/logs');
define ('LOG_QUERIES', false);
define ('DEBUG_CONVERSION', false);
define ('CONVERSION_LOG', LOG . '/converter.log');
define ('QUERY_LOG', LOG . '/query.log');
define ('DB_ERR_LOG', LOG . '/db_errors.log');
define ('UPLOAD_PATH', DOC_ROOT . '/cc-content/uploads');
define ('EMAIL_PATH', DOC_ROOT . '/cc-content/emails');
define ('UPDATE_URL', 'http://update.cumulusclips.org');
define ('VIDEO_SIZE_LIMIT', 102000000);
ini_set ('max_execution_time', 3600);


// Create the config object
$config = new stdClass();

// General Site Settings
$config->php = '/usr/bin/php';
$config->ffmpeg = '/usr/bin/ffmpeg';
$config->pagination_page_limit = 9;
$config->accepted_video_extensions = array ('flv', 'wmv', 'avi', 'ogg', 'mpg', 'mp4', 'mov','m4v');
//$config->flv_bucket_url = 'http://cumulus/cc-content/uploads/flv';
//$config->mp4_bucket_url = 'http://cumulus/cc-content/uploads/mp4';
//$config->thumb_bucket_url = 'http://cumulus/cc-content/uploads/thumbs';
$config->flv_bucket_url = 'http://c1495122.cdn.cloudfiles.rackspacecloud.com';
$config->mp4_bucket_url = 'http://c1488222.cdn.cloudfiles.rackspacecloud.com';
$config->thumb_bucket_url = 'http://c1495132.cdn.cloudfiles.rackspacecloud.com';





/***************
TODO's CHECKLIST
***************/

// TODO Build admin panel
    // TODO Browse Themes
    // TODO Activate theme
    // TODO Delete theme
    // TODO Preview theme


// TODO Add 'View My Profile' link in my account / top nav
// TODO Lockdown upload's directory with .htaccess rules

?>