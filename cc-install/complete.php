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
$dbc = @mysql_connect ($settings->db_hostname, $settings->db_username, $settings->db_password);
@mysql_select_db ($settings->db_name, $dbc);

// Save settings
$query = "INSERT INTO " . $settings->db_prefix . "settings (name, value) VALUES";
$query .= " ('base_url', '$settings->base_url'),";
$query .= " ('secret_key', '" . md5(time()) . "'),";
$query .= " ('sitename', '" . mysql_real_escape_string ($settings->sitename) . "'),";
$query .= " ('admin_email', '$settings->admin_email'),";
$query .= " ('enable_uploads', '$settings->uploads_enabled'),";
$query .= " ('ffmpeg', '$settings->ffmpeg'),";
$query .= " ('qtfaststart', '$settings->qtfaststart'),";
$query .= " ('php', '$settings->php')";
$result = @mysql_query ($query);

// Save admin user
$query = "INSERT INTO " . $settings->db_prefix . "users (username, password, email, date_created, status, role, released) VALUES";
$query .= "('$settings->admin_username', '" . md5 ($settings->admin_password) . "', '$settings->admin_email', NOW(), 'active', 'admin', 1)";
$result = @mysql_query ($query);
$id = @mysql_insert_id();

// Save admin user's privacy settings
$query = "INSERT INTO " . $settings->db_prefix . "privacy (user_id) VALUES ($id)";
$result = @mysql_query ($query);

// Create admin user's favorites playlist
$query = "INSERT INTO " . $settings->db_prefix . "playlists (user_id, public, type, date_created) VALUES ($id, 0, 'favorites', NOW())";
$result = @mysql_query($query);

// Create admin user's watch later playlist
$query = "INSERT INTO " . $settings->db_prefix . "playlists (user_id, public, type, date_created) VALUES ($id, 0, 'watch_later', NOW())";
$result = @mysql_query($query);

// Log user into admin panel
$_SESSION['loggedInUserId'] = $id;
header ("Location: " . $settings->base_url . '/cc-admin/?first_run');