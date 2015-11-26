<?php

$patch_file_content = null;
$patch_file = null;
define ('DOC_ROOT', dirname (dirname (__FILE__)));
include (DOC_ROOT . '/cc-core/config/config.php');


// Connect to DB
$dbc = @mysql_connect (DB_HOST, DB_USER, DB_PASS);
@mysql_select_db (DB_NAME, $dbc);


// Retrieve Current Version
$query = "SELECT value FROM " . DB_PREFIX . "settings WHERE name = 'version'";
$result = @mysql_query ($query);
$row = mysql_fetch_object ($result);
$version = (empty ($row->value)) ? '110' : str_pad (str_replace ('.', '', $row->value), 3, '0');  // DB Version var. was introduced in v.1.1.1


try {

    // Call to mothership and retrieve patch file
    $curl_handle = curl_init();
    curl_setopt ($curl_handle, CURLOPT_URL, 'http://mothership.cumulusclips.org/updates/patches/?version=' . $version);
    curl_setopt ($curl_handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt ($curl_handle, CURLOPT_FOLLOWLOCATION, true);
    $patch_file_content = curl_exec ($curl_handle);
    curl_close ($curl_handle);
    if (empty ($patch_file_content)) throw new Exception ("<p>Your database is up to date!</p>");

    
    // Create and load db patch file
    $patch_file = DOC_ROOT . '/cc-install/patch_file.php';
    if (false === @file_put_contents ($patch_file, $patch_file_content)) throw new Exception ("<p>Unable to create db patch file at: $patch_file.</p><p>Please make sure the webserver/PHP has write permissions to: " . DOC_ROOT . "/cc-install</p>");
    include_once ($patch_file);


    // Re-connect to db server in case it went away
    if (!mysql_ping ($dbc)) {
        $dbc = @mysql_connect (DB_HOST, DB_USER, DB_PASS);
        @mysql_select_db (DB_NAME, $dbc);
    }


    // Execute DB modifications queries
    reset ($perform_update);
    foreach ($perform_update as $version) {
        $db_update_queries = call_user_func ('db_update_' . $version);
        foreach ($db_update_queries as $query) @mysql_query ($query);
    }

} catch (Exception $e) {
    exit($e->getMessage());
}

echo '<p>Your database has been updated!</p>';