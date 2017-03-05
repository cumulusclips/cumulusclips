<?php

include (DOC_ROOT . '/cc-core/lib/Filesystem.php');

class FilesystemInstaller extends Filesystem
{
    /**
     * Open an FTP connection to filesystem with FTP settings from config
     * @throws Exception If errors are encountered while connecting or logging in
     */
    protected static function _open()
    {
        // Set FTP login settings
        global $settings;
        if (isset($settings->ftp_hostname, $settings->ftp_username, $settings->ftp_password, $settings->ftp_path, $settings->ftp_ssl)) {
            self::$_ftp_hostname = $settings->ftp_hostname;
            self::$_ftp_username = $settings->ftp_username;
            self::$_ftp_password = $settings->ftp_password;
            self::$_ftp_path = $settings->ftp_path;
            self::$_ftp_ssl = $settings->ftp_ssl;
        } else {
            throw new Exception("CumulusClips is unable to perform filesystem operations natively. Please provide FTP credentials.");
        }

        // Connect to FTP host
        if (self::$_ftp_ssl) {
            if (!function_exists ('ftp_ssl_connect')) throw new Exception("Your host doesn't support FTP over SSL connections.");
            self::$_ftp_stream = @ftp_ssl_connect(self::$_ftp_hostname);
        } else {
            self::$_ftp_stream = @ftp_connect(self::$_ftp_hostname);
        }
        if (!self::$_ftp_stream) throw new Exception("Unable to connect to FTP host (" . self::$_ftp_hostname . ")");

        // Login with username and password
        if (!ftp_login(self::$_ftp_stream, self::$_ftp_username, self::$_ftp_password)) {
            throw new Exception("Unable to login to FTP server (Username: '" . self::$_ftp_username . "', Password: '" . self::$_ftp_password. "')");
        }
    }
}
