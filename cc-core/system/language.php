<?php

### Created on March 15, 2009
### Created by Miguel A. Hurtado
### This script performs all the user actions for a video via AJAX


// Include required files
include ('../config/bootstrap.php');



// Return requested string
if (!empty ($_POST['node'])) {
    if (!empty ($_POST['replacements']) && is_array ($_POST['replacements'])) {
        $replacements = $_POST['replacements'];
    } else {
        $replacements = array();
    }
    $string = Language::GetText ($_POST['node'], $replacements);
    echo $string ? $string : '';
    exit();
}

?>