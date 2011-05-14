<?php

### Created on March 8, 2009
### Created by Miguel A. Hurtado
### This script logs users out


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');

// Retrieve user data if logged in
if (User::LoginCheck()) {
    User::Logout(); // Plugin Hook is within method
    header ('Location: ' . HOST . '/');
} else {
    header ('Location: ' . HOST . '/myaccount/');
}

?>