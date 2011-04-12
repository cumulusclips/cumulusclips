<?php

### Created on January 23, 2010
### Created by Miguel A. Hurtado
### This script displays the registered users


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
$page_title = 'Browse Users - Admin Techie Videos';
$content_file = 'admin/users.tpl';


$query = "SELECT * FROM users";
$result = $db->Query ($query);


include (THEMES . '/layouts/admin.layout.tpl');

?>