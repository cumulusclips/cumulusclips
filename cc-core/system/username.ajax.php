<?php

### Created on March 9, 2009
### Created by Miguel A. Hurtado
### This script checks if a username has already been taken


// Include required files
include ('../config/bootstrap.php');
App::LoadClass ('User');
Plugin::Trigger ('username.ajax.start');



### Check if username is in use
if (!empty ($_POST['username']) && !ctype_space ($_POST['username']) && strlen ($_POST['username'] >= 4)) {
    sleep (1);
    if (User::Exist (array ('username' => $_POST['username']))) {
        echo 'FALSE';
    } else {
        echo 'TRUE';
    }
} else {
    echo 'FALSE';
}

?>