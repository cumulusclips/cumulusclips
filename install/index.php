<?php

session_start();

// Retrieve base URL
$PROTOCOL = (!empty ($_SERVER['HTTPS'])) ? 'https://' : 'http://';
$HOSTNAME = $_SERVER['SERVER_NAME'];
$PATH = rtrim (dirname (preg_replace ('/\?' . $_SERVER['QUERY_STRING'] . '/', '', $_SERVER['REQUEST_URI'])), '/');
$HOST = $PROTOCOL . $HOSTNAME . $PATH;


// Establish vars
define ('DOC_ROOT', dirname (dirname (__FILE__)));
define ('INSTALL', DOC_ROOT . '/install');
define ('HOST', $HOST);
$action = null;


// Load / Set settings
if (isset ($_SESSION['settings'])) {
    $settings = unserialize ($_SESSION['settings']);
} else {
    $settings = new stdClass();
}


// Determine which page to load
if (isset ($_GET['requirements'])) $action = 'requirements';
if (isset ($_GET['ftp'])) $action = 'ftp';
if (isset ($_GET['database'])) $action = 'database';
if (isset ($_GET['site-details'])) $action = 'site-details';
if (isset ($_GET['complete'])) $action = 'complete';
if (!$action) $action = 'welcome';

include_once (INSTALL . "/$action.php");

?>