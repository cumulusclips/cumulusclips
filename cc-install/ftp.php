<?php

// Send user to appropriate step
if (!isset ($settings->completed)) {
    header ("Location: " . HOST . '/cc-install/');
    exit();
} else if (!in_array ('requirements', $settings->completed)) {
    header ("Location: " . HOST . '/cc-install/?requirements');
    exit();
} else if (in_array ('ftp', $settings->completed)) {
    header ("Location: " . HOST . '/cc-install/?database');
    exit();
}

// Establish needed vars.
$page_title = 'CumulusClips - FTP Connection';
$native = null;
$errors = array();
$error_msg = null;

// Check if Apache owns directories. If so, this means that native filesystem methods can be used and FTP isn't needed
if (posix_getuid() == fileowner(DOC_ROOT) && is_writable(DOC_ROOT)) {
    $settings->ftp_hostname = '';
    $settings->ftp_username = '';
    $settings->ftp_password = '';
    $settings->ftp_path = '';
    $settings->ftp_ssl = false;
    $settings->completed[] = 'ftp';
    $_SESSION['settings'] = serialize ($settings);
    header ("Location: " . HOST . '/cc-install/?database');
    exit();
}

// Validate form if submitted
if (isset ($_POST['submitted'])) {

    // Validate hostname
    $pattern = '/^[a-z0-9][a-z0-9\.\-]*$/i';
    if (!empty ($_POST['hostname']) && !ctype_space ($_POST['hostname']) && preg_match ($pattern, $_POST['hostname'])) {
        $hostname = trim ($_POST['hostname']);
    } else {
        $errors['hostname'] = 'A valid hostname is needed';
    }

    // Validate username
    if (!empty ($_POST['username']) && !ctype_space ($_POST['username'])) {
        $username = trim ($_POST['username']);
    } else {
        $errors['username'] = 'A valid username is needed';
    }

    // Validate password
    if (!empty ($_POST['password']) && !ctype_space ($_POST['password'])) {
        $password = trim ($_POST['password']);
    } else {
        $errors['password'] = 'A valid password is needed';
    }

    // Validate path
    if (!empty ($_POST['path']) && !ctype_space ($_POST['path'])) {
        $path = rtrim ($_POST['path'], '/');
    } else {
        $errors['path'] = 'A valid path is needed';
    }

    // Validate connection method
    if (isset ($_POST['method']) && in_array ($_POST['method'], array ('ftp', 'ftps'))) {
        $method = $_POST['method'];
    } else {
        $errors['method'] = 'A valid connection method is needed';
    }

    if (empty ($errors)) {

        // Create and populate local file stream
        $handle = tmpfile();
        fwrite ($handle, 'FTP Test');

        try {
            // Connect to FTP server
            if ($method == 'ftp') {
                $stream = @ftp_connect ($hostname);
            } else {
                if (!function_exists('ftp_ssl_connect')) throw new Exception ("Your host doesn't support FTP over SSL connections.");
                $stream = @ftp_ssl_connect ($hostname);
            }
            if (!$stream) throw new Exception ("We were unable to connect to the FTP server you specified, please verify it is correct.");

            // Login to FTP server
            $login = @ftp_login ($stream, $username, $password);
            if (!$login) {
                $error_msg = "We were unable to login to the FTP server with the ";
                $error_msg .= "credentials you specified. Please verify they're ";
                $error_msg .= "correct and try again.";
                throw new Exception ($error_msg);
            }

            // Create test file
            $test_file = $path . '/ftp-test' . time();
            if (!@ftp_chdir ($stream, $path)) throw new Exception ("We were unable to navigate to the CumulusClips directory. Please verify your ftp path is correct and your account has access.");
            if (!@ftp_fput ($stream, $test_file, $handle, FTP_BINARY)) throw new Exception ("We were unable create a test file. Please verify your account has write access.");
            if (!@ftp_delete ($stream, $test_file)) throw new Exception ("We were unable delete our test file. Please verify your account has the ability to delete files.");
            @ftp_close ($stream);
            fclose ($handle);

            // Store information & redirect user
            $settings->ftp_hostname = $hostname;
            $settings->ftp_username = $username;
            $settings->ftp_password = $password;
            $settings->ftp_path = $path;
            $settings->ftp_ssl = ($method == 'ftps') ? true : false;
            $settings->completed[] = 'ftp';
            $_SESSION['settings'] = serialize ($settings);
            header ("Location: " . HOST . '/cc-install/?database');
            exit();

        } catch (Exception $e) {
            $error_msg = $e->getMessage();
            @ftp_close ($stream);
            fclose ($handle);
        }
    } else {
        $error_msg = "Errors were found. Please correct them and try again.<br /><br /> - ";
        $error_msg .= implode ("<br /> - ", $errors);
    }
}

// Output page
include_once ('views/ftp.tpl');