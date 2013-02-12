<?php

include_once (DOC_ROOT . '/cc-core/lib/Filesystem.php');

class FilesystemInstaller extends Filesystem {


    static function Open() {

        // Check if native PHP methods should be used - Test 1
        self::$native = (is_writable (DOC_ROOT) && getmyuid() == fileowner (DOC_ROOT)) ? true : false;

        // Check if native PHP methods should be used - Test 2
        if (self::$native) {

            // Create temporary file
            $native_check_file = DOC_ROOT . '/native-check' . time();
            $handle = @fopen ($native_check_file, 'w');
            @fwrite ($handle, 'Native Check');

            // Check if webserver/PHP has filesystem access
            self::$native = (fileowner ($native_check_file) == getmyuid()) ? true : false;

            // Remove temporary file
            @fclose ($handle);
            @unlink ($native_check_file);
            
        }


        // Login to server via FTP if PHP doesn't have write access
        if (!self::$native) {

            // Set FTP login settings
            global $settings;
            self::$ftp_hostname = $settings->ftp_hostname;
            self::$ftp_username = $settings->ftp_username;
            self::$ftp_password = $settings->ftp_password;
            self::$ftp_path = $settings->ftp_path;
            self::$ftp_ssl = $settings->ftp_ssl;

            // Connect to FTP host
            if (self::$ftp_ssl) {
                if (!function_exists('ftp_ssl_connect')) throw new Exception ("Your host doesn't support FTP over SSL connections.");
                self::$ftp_stream = @ftp_ssl_connect (self::$ftp_hostname);
            } else {
                self::$ftp_stream = @ftp_connect (self::$ftp_hostname);
            }
            if (!self::$ftp_stream) throw new Exception ("Unable to connect to FTP host (" . self::$ftp_hostname . ")");



            // Login with username and password
            if (!@ftp_login (self::$ftp_stream, self::$ftp_username, self::$ftp_password)) {
                throw new Exception ("Unable to login to FTP server (Username: '" . self::$ftp_username . "', Password: '" . self::$ftp_password. "')");
            }

        }

        return (self::$native) ? 'native' : 'ftp';

    }

}

?>