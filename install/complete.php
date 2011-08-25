<?php

// Send user to appropriate step
if (!isset ($settings->completed)) {
    header ("Location: " . HOST . '/install/');
    exit();
} else if (!in_array ('site-details', $settings->completed)) {
    header ("Location: " . HOST . '/install/?site-details');
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
$query .= " ('qt_faststart', '$settings->qt_faststart'),";
$query .= " ('php', '$settings->php')";
$result = @mysql_query ($query);

// Save admin user
$query = "INSERT INTO " . $settings->db_prefix . "users (username, password, email, date_created, status, released) VALUES";
$query .= "('$settings->admin_username', '" . md5 ($settings->admin_password) . "', '$settings->admin_email', NOW(), 'active', 1)";
$result = @mysql_query ($query);


// Log user into admin panel
$id = @mysql_insert_id();
$_SESSION['user_id'] = $id;
header ("Location: " . $settings->base_url . '/cc-admin/?first_run');

?>