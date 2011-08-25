<?php

// Send user to appropriate step
if (!isset ($settings->completed) || !in_array ('welcome', $settings->completed)) {
    header ("Location: " . HOST . '/install/');
    exit();
} else if (in_array ('requirements', $settings->completed)) {
    header ("Location: " . HOST . '/install/?ftp');
    exit();
}


// Establish needed vars.
$page_title = 'CumulusClips - Requirements';
$errors = null;
$warnings = null;
$continue = null;



// Check PHP version
$version = explode ('.', phpversion(), 3);
if (count ($version) > 1){
    
    $current_version = (int) $version[0] . '.' . $version[1];
    if (5.2 > $current_version) {
        $version = false;
        $errors = true;
    } else {
        $version = true;
    }
    
} else if ($version[0] < 6) {
    $version = false;
    $errors = true;
}




// Retrieve php path
@exec ('which php', $which_results);
if (empty ($which_results)) {

    // Find PHP path using whereis
    @exec ('whereis php', $whereis_results);
    $whereis_results = preg_replace ('/^php:\s?/','', $whereis_results[0]);
    if (!empty ($whereis_results)) {
        $path = explode (' ', $whereis_results[0]);
        $settings->php = $path[0];
        $php_path = true;
    } else {
        $settings->php = '';
        $warnings = true;
        $php_path = false;
        $disable_uploads = true;
    }

} else {
    $settings->php = $which_results[0];
    $php_path = true;
}




// Check if FFMPEG is installed (using which)
@exec ('which ffmpeg', $which_results_ffmpeg);
if (empty ($which_results_ffmpeg)) {
    
    // Check if FFMPEG is installed (using whereis)
    @exec ('whereis ffmpeg', $whereis_results_ffmpeg);
    $whereis_results_ffmpeg = preg_replace ('/^ffmpeg:\s?/','', $whereis_results_ffmpeg[0]);
    if (empty ($whereis_results_ffmpeg)) {
        $settings->ffmpeg = '';
        $ffmpeg = false;
        $disable_uploads = true;
        $warnings = true;
    } else {
        $path_ffmpeg = explode (' ', $whereis_results_ffmpeg[0]);
        $settings->ffmpeg = $path_ffmpeg[0];
        $ffmpeg = true;
    }
    
} else {
    $settings->ffmpeg = $which_results_ffmpeg[0];
    $ffmpeg = true;
}




// Check if qt-faststart is installed (using which)
@exec ('which qt-faststart', $which_results_faststart);
if (empty ($which_results_faststart)) {

    // Check if qt-faststart is installed (using whereis)
    @exec ('whereis qt-faststart', $whereis_results_faststart);
    $whereis_results_faststart = preg_replace ('/^qt\-faststart:\s?/','', $whereis_results_faststart[0]);
    if (empty ($whereis_results_faststart)) {
        $settings->qt_faststart = '';
        $qt_faststart = false;
        $disable_uploads = true;
        $warnings = true;
    } else {
        $path_faststart = explode (' ', $whereis_results_faststart[0]);
        $settings->qt_faststart = $path_faststart[0];
        $qt_faststart = true;
    }

} else {
    $settings->qt_faststart = $which_results_faststart[0];
    $qt_faststart = true;
}




// Verify 'zip' php module is loaded
$zip = extension_loaded ('zip');
if (!$zip) $errors = true;


// Verify 'json' php module is loaded
$json = extension_loaded ('json');
if (!$json) $errors = true;


// Verify 'ftp' php module is loaded
$ftp = extension_loaded ('ftp');
if (!$ftp) $errors = true;


// Verify 'simplexml' php module is loaded
$simplexml = extension_loaded ('simplexml');
if (!$simplexml) $errors = true;


// Verify 'short_open_tag' php setting is valid
$short_open_tag = ini_get ('short_open_tag');
$short_open_tag = (!empty ($short_open_tag)) ? true : false;
if (!$short_open_tag) $errors = true;


// Verify 'file_uploads' php setting is valid
$file_uploads = ini_get ('file_uploads');
$file_uploads = (!empty ($file_uploads)) ? true : false;
if (!$file_uploads) $errors = true;


// Verify 'max_execution_time' php setting is valid
$max_execution_time = ini_get ('max_execution_time');
if ($max_execution_time < 1200) {
    $max_execution_time = false;
    $warnings = true;
}


// Verify 'post_max_size' php setting is valid
$post_max_size = strtoupper (ini_get('post_max_size'));
if (substr ($post_max_size, -1) != 'M' || 99 > (int) rtrim ($post_max_size,'M')) {
    $post_max_size = false;
    $warnings = true;
}


// Verify 'upload_max_filesize' php setting is valid
$upload_max_filesize = strtoupper (ini_get('upload_max_filesize'));
if (substr ($upload_max_filesize, -1) != 'M' || 99 > (int) rtrim ($upload_max_filesize,'M')) {
    $upload_max_filesize = false;
    $warnings = true;
}


// Verify logs dir. is writeable by the webserver
$logs = is_writable (DOC_ROOT . '/cc-core/logs');
if (!$logs) $errors = true;


// Verify uploads dir. is writeable by the webserver
$uploads = is_writable (DOC_ROOT . '/cc-content/uploads');
if (!$uploads) $errors = true;


// Continue to next step if no errors
if (!$errors) {
    $settings->uploads_enabled = (isset ($disable_uploads)) ? false : true;
    $settings->completed[] = 'requirements';
    $_SESSION['settings'] = serialize ($settings);
    $continue = true;
    if (!$warnings) {
        header ("Location: " . HOST . '/install/?ftp');
        exit();
    }
}


// Output page
include_once (INSTALL . '/views/requirements.tpl');

?>