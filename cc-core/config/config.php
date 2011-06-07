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
define ('VIDEO_SIZE_LIMIT', 102000000);
ini_set ('max_execution_time', 3600);


// Create the config object
$config = new stdClass();

// General Site Settings
$config->php = '/usr/bin/php';
$config->ffmpeg = '/usr/bin/ffmpeg';
$config->pagination_page_limit = 9;
$config->accepted_video_extensions = array ('flv', 'wmv', 'avi', 'ogg', 'mpg', 'mp4', 'mov','m4v');


// Video transcoding settings
define ('ENCODING_FTP', 'ftp://username:password@192.168.1.123');
$config->en_user = '2317';
$config->en_key = '87d893bb7a4854985e7653e36fdc6e44';
$config->rs_user = 'mahurtado66';
$config->rs_key = 'd8b78c654d1e6eb572e7b0d41d2d9ce8';
//$config->flv_bucket_url = 'http://cumulus/cc-content/uploads/flv';
//$config->mp4_bucket_url = 'http://cumulus/cc-content/uploads/mp4';
//$config->thumb_bucket_url = 'http://cumulus/cc-content/uploads/thumbs';
$config->flv_bucket_url = 'http://c1495122.cdn.cloudfiles.rackspacecloud.com';
$config->mp4_bucket_url = 'http://c1488222.cdn.cloudfiles.rackspacecloud.com';
$config->thumb_bucket_url = 'http://c1495132.cdn.cloudfiles.rackspacecloud.com';
$config->flv_bucket = 'flv';
$config->mp4_bucket = 'mp4';
$config->thumb_bucket = 'thumbs';





/***************
TODO's CHECKLIST
****************/

// TODO Build admin panel
//    TODO Remove flag records during manual ban (video)
//    TODO Remove flag records during manual ban (members)
//    TODO Remove flag records during manual ban (comments)
//    TODO Feature video option
//    TODO Make Make Delete methods recursive instead of direct DB queries


// TODO Add 'View My Profile' link in my account / top nav
// TODO Lockdown upload's directory with .htaccess rules

?>