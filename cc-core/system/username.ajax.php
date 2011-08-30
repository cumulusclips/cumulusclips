<?php

### Created on March 9, 2009
### Created by Miguel A. Hurtado
### This script checks if a username has already been taken


// Include required files
include_once (dirname (dirname (__FILE__)) . '/config/bootstrap.php');
App::LoadClass ('User');
Plugin::Trigger ('username.ajax.start');



### Check if username is in use
if (!empty ($_POST['username']) && strlen ($_POST['username']) >= 4) {

    if (User::Exist (array ('username' => $_POST['username']))) {
        echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText('error_username_unavailable')));
    } else {
        echo json_encode (array ('result' => 1, 'msg' => (string) Language::GetText('username_available')));
    }
    
} else {
    echo json_encode (array ('result' => 0, 'msg' => (string) Language::GetText('username_minimum')));
}

?>