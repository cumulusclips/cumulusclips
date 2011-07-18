<?php

// Send user to appropriate step
if (!in_array ('site-details', $settings->completed)) {
    header ("Location: " . HOST . '/install/?site-details');
    exit();
}


// Establish needed vars.
$page_title = 'CumulusClips - Complete';
unset ($_SESSION['settings']);
$error_msg = null;


### Save provided information to the database
$dbc = mysql_connect ($settings->db_hostname, $settings->db_username, $settings->db_password);
mysql_select_db ($settings->db_name, $dbc);

// Save settings
$query = "INSERT INTO " . $settings->db_prefix . "settings (name, value) VALUES";
$query .= " ('ftp_hostname', '$settings->ftp_hostname'),";
$query .= " ('ftp_username', '$settings->ftp_username'),";
$query .= " ('ftp_password', '$settings->ftp_password'),";
$query .= " ('ftp_protocol', '$settings->ftp_protocol'),";
$query .= " ('base_url', '$settings->base_url'),";
$query .= " ('sitename', '" . mysql_real_escape_string ($settings->sitename) . "'),";
$query .= " ('uploads_enabled', '$settings->uploads_enabled')";
$result = mysql_query ($query);

// Save admin user
$query = "INSERT INTO " . $settings->db_prefix . "users (username, password, email, date_created, status, released) VALUES";
$query .= "('$settings->admin_username', '" . md5 ($settings->admin_password) . "', '$settings->admin_email', NOW(), 'active', 1)";
$result = mysql_query ($query);



// Delete install files/dir
include_once (INSTALL . '/includes/Filesystem.php');
try {
    Filesystem::Open();
    Filesystem::Delete (INSTALL);
    Filesystem::Close();
} catch (Exception $e) {
    $error_msg = 'Unable to delete the install directory.';
}


// Output page
include_once (INSTALL . '/views/complete.tpl');

?>