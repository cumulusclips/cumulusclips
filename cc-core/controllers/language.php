<?php

### Created on March 15, 2009
### Created by Miguel A. Hurtado
### This script performs all the user actions for a video via AJAX


// Include required files
include ('../config/bootstrap.php');



// Return requested string
if (!empty ($_GET['node'])) {
    if (!empty ($_GET['replacements'])) {
        parse_str ($_GET['replacements'], $replacements);
    } else {
        $replacements = array();
    }
    $string = Language::GetText ($_GET['node'], $replacements);
    echo $string ? $string : '';
    exit();
}

?>