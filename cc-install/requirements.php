<?php

// Send user to appropriate step
if (!isset ($settings->completed) || !in_array ('welcome', $settings->completed)) {
    header ("Location: " . HOST . '/cc-install/');
    exit();
} else if (in_array ('requirements', $settings->completed)) {
    header ("Location: " . HOST . '/cc-install/?ftp');
    exit();
}


// Establish needed vars.
$page_title = 'CumulusClips - Requirements';
$errors = null;
$warnings = null;
$continue = null;



// Check PHP version
if (version_compare(phpversion(), '5.3.0') >= 0) {
    $version = true;
} else {
    $version = false;
    $errors = true;
}


// Retrieve php-cli path
@exec('whereis php', $whereis_results);
$whereis_results = explode (' ', preg_replace ('/^php:\s?/','', $whereis_results[0]));
if (!empty ($whereis_results)) {
    foreach ($whereis_results as $phpExe) {
        if (!is_executable($phpExe)) continue;
        @exec($phpExe . ' -r "' . "echo 'cliBinary';" . '" 2>&1 | grep cliBinary', $phpCliResults);
        $phpCliResults = implode(' ', $phpCliResults);
        if (!empty($phpCliResults)) {
            $settings->php = $phpExe;
            $php_path = true;
            break;
        }
    }
}

if (!isset($php_path)) {
    $settings->php = '';
    $warnings = true;
    $php_path = false;
    $disable_uploads = true;
}



// Retrieve disabled functions
$disabledFunctions = explode(',', ini_get('disable_functions'));

// Verify exec function is available
if (function_exists('exec') && !in_array('exec', $disabledFunctions)) {
    $exec = true;
} else {
    $errors = true;
    $exec = false;
}


// Verify 'posix' php module is loaded
$posix = extension_loaded ('posix');
if (!$posix) $errors = true;


// Verify 'curl' php module is loaded
$curl = extension_loaded ('curl');
if (!$curl) $errors = true;


// Verify 'gd' php module is loaded
$gd = extension_loaded ('gd');
if (!$gd) $errors = true;


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


// Determine FFMPEG & qt-faststart paths according to proccessor architecture
if ($posix) {
    $systemInfo = posix_uname();
    if (strpos($systemInfo['machine'], '64') !== false) {
        $settings->ffmpeg = DOC_ROOT . '/cc-core/system/bin/ffmpeg-64-bit/ffmpeg';
        $settings->qtfaststart = DOC_ROOT . '/cc-core/system/bin/ffmpeg-64-bit/qt-faststart';
    } else {
        $settings->ffmpeg = DOC_ROOT . '/cc-core/system/bin/ffmpeg-32-bit/ffmpeg';
        $settings->qtfaststart = DOC_ROOT . '/cc-core/system/bin/ffmpeg-32-bit/qt-faststart';
    }
}


// Continue to next step if no errors
if (!$errors) {
    $settings->uploads_enabled = (isset ($disable_uploads)) ? false : true;
    $settings->completed[] = 'requirements';
    $_SESSION['settings'] = serialize ($settings);
    $continue = true;
    if (!$warnings) {
        header ("Location: " . HOST . '/cc-install/?ftp');
        exit();
    }
}


// Output page
include_once (INSTALL . '/views/requirements.tpl');