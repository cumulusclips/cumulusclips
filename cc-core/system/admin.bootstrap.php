<?php

// Include Main Bootstrap
include ('bootstrap.php');

// Pre-Output Work
define ('ADMIN', HOST . '/cc-admin');
if (!headers_sent()) {

    // Create admin settings cookie
    if (empty ($_COOKIE['cc_admin_settings'])) {
        $cc_admin_settings = array(
            'dashboard'     => 0,
            'videos'        => 0,
            'members'       => 0,
            'comments'      => 0,
            'flags'         => 0,
            'pages'         => 0,
            'appearance'    => 0,
            'plugins'       => 0,
            'settings'      => 0
        );
        setcookie ('cc_admin_settings', http_build_query ($cc_admin_settings));
    }
}

// Check for updates (once per session)
if (!isset ($_SESSION['updates_available'])) {
    $updates_available = Functions::UpdateCheck();
    $_SESSION['updates_available'] = ($updates_available) ? serialize ($updates_available) : false;
}