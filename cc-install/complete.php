<?php

// Send user to appropriate step
if (!isset ($settings->completed)) {
    header ("Location: " . HOST . '/cc-install/');
    exit();
} else if (!in_array ('site-details', $settings->completed)) {
    header ("Location: " . HOST . '/cc-install/?site-details');
    exit();
}


// Establish needed vars.
$page_title = 'CumulusClips - Complete';
unset ($_SESSION['settings']);
$error_msg = null;


### Save provided information to the database
$pdo = new PDO(
    'mysql:host=' . $settings->db_hostname . ';port=' . $settings->db_port . ';dbname=' . $settings->db_name,
    $settings->db_username,
    $settings->db_password,
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
);

// Save settings
$bindParams = array(
    ':baseUrl' => $settings->base_url,
    ':secretKey' => md5(time()),
    ':sitename' => $settings->sitename,
    ':adminEmail' => $settings->admin_email,
    ':enableUploads' => $settings->uploads_enabled,
    ':ffmpeg' => $settings->ffmpeg,
    ':qtfaststart' => $settings->qtfaststart,
    ':php' => $settings->php
);
$query = <<<QUERY
INSERT INTO {$settings->db_prefix}settings (name, value) VALUES
    ('base_url', :baseUrl),
    ('secret_key', :secretKey),
    ('sitename', :sitename),
    ('admin_email', :adminEmail),
    ('enable_uploads', :enableUploads),
    ('ffmpeg', :ffmpeg),
    ('qtfaststart', :qtfaststart),
    ('php', :php)
QUERY;
$pdoStatement = $pdo->prepare($query);
$pdoStatement->execute($bindParams);


// Save admin user
$bindParams = array(
    ':username' => $settings->admin_username,
    ':password' => md5($settings->admin_password),
    ':email' => $settings->admin_email
);
$query = <<<QUERY
INSERT INTO {$settings->db_prefix}users
    (username, password, email, date_created, status, role, released) VALUES
    (:username, :password, :email, NOW(), 'active', 'admin', 1)
QUERY;
$pdoStatement = $pdo->prepare($query);
$pdoStatement->execute($bindParams);


$id = $pdo->lastInsertId();
$bindParams = array(':id' => $id);


// Save admin user's privacy settings
$query = "INSERT INTO {$settings->db_prefix}privacy (user_id) VALUES (:id)";
$pdoStatement = $pdo->prepare($query);
$pdoStatement->execute($bindParams);


// Create admin user's watch later & favorites playlist
$query = <<<QUERY
INSERT INTO {$settings->db_prefix}playlists (user_id, public, type, date_created) VALUES
    (:id, 0, 'favorites', NOW()),
    (:id, 0, 'watch_later', NOW())
QUERY;
$pdoStatement = $pdo->prepare($query);
$pdoStatement->execute($bindParams);


// Destroy installer session
$params = session_get_cookie_params();
setcookie(
    session_name(),
    '',
    time() - 42000,
    $params["path"],
    $params["domain"],
    $params["secure"],
    $params["httponly"]
);
$_SESSION = array();
session_destroy();


// Authenticate admin user
ini_set('session.name', 'EID');
ini_set('session.use_strict_mode', true);
ini_set('session.cookie_httponly', true);
ini_set('session.use_cookies', true);
ini_set('session.use_only_cookies', true);
ini_set('session.use_trans_sid', true);
ini_set('session.cookie_domain', parse_url($settings->base_url, PHP_URL_HOST));
ini_set('session.cookie_path', parse_url($settings->base_url, PHP_URL_PATH) ?: '/');
session_start();
$_SESSION['loggedInUserId'] = $id;


// Direct user into admin panel
header ("Location: " . $settings->base_url . '/cc-admin/?first_run');
