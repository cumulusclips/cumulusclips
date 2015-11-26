<?php

// Send user to appropriate step
if (isset ($settings->completed) && in_array ('welcome', $settings->completed)) {
    header ("Location: " . HOST . '/cc-install/?requirements');
    exit();
}

$page_title = 'CumulusClips - Welcome';
$settings->completed = array ('welcome');
$_SESSION['settings'] = serialize ($settings);


// Output page
include_once (INSTALL . '/views/welcome.tpl');