<?php

### Created on March 9, 2009
### Created by Miguel A. Hurtado
### This script checks if a username has already been taken


// Include required files
include ($_SERVER['DOCUMENT_ROOT'] . '/config/bootstrap.php');
include (DOC_ROOT . '/includes/functions.php');
App::LoadClass ('DBConnection.php');
App::LoadClass ('KillApp.php');
App::LoadClass ('User.php');
include (DOC_ROOT . '/includes/username_reserve.php');


// Establish page variables, objects, arrays, etc
$KillApp = new KillApp;
$db = new DBConnection ($KillApp);



### Check if username is in use
if (!empty ($_POST['username']) && !ctype_space ($_POST['username'])) {

	sleep (1);	// Just to show off the loading gif!!! ;-)

	$username = $db->Escape ($_POST['username']);
	$data = array ('username' => $username);
    if (User::Exist ($data, $db) || in_array ($username, $user_reserve)) {
		echo 'FALSE';
	} else {
		echo 'TRUE';
	}

}

?>