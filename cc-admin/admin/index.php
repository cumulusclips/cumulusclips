<?php

### Created on January 29, 2010
### Created by Miguel A. Hurtado
### This script displays the admin homepage


// Include required files
include ($_SERVER['DOCUMENT_ROOT'] . '/config/bootstrap.php');
include (DOC_ROOT . '/includes/functions.php');
App::LoadClass ('DBConnection.php');
App::LoadClass ('KillApp.php');
App::LoadClass ('Login.php');
App::LoadClass ('User.php');


// Establish page variables, objects, arrays, etc
session_start();
$KillApp = new KillApp;
$db = new DBConnection ($KillApp);
$login = new Login ($db);
$logged_in = $login->LoginCheck();
if ($logged_in != 22) Throw404(); // Only allow TechieVideos user
$user = new User ($logged_in, $db);
$page_title = 'Admin Techie Videos';
$content_file = 'admin/index.tpl';


include (THEMES . '/layouts/admin.layout.tpl');

?>