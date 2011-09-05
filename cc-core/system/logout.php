<?php

// Include required files
include_once (dirname (dirname (__FILE__)) . '/config/bootstrap.php');
App::LoadClass ('User');

// Retrieve user data if logged in
if (User::LoginCheck()) {
    User::Logout(); // Plugin Hook is within method
    header ('Location: ' . HOST . '/');
} else {
    header ('Location: ' . HOST . '/myaccount/');
}

?>