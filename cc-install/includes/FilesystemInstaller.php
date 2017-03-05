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
            static::$_ftp_hostname = $settings->ftp_hostname;
            static::$_ftp_username = $settings->ftp_username;
            static::$_ftp_password = $settings->ftp_password;
            static::$_ftp_path = $settings->ftp_path;
            static::$_ftp_ssl = $settings->ftp_ssl;
        } else {
            throw new Exception("CumulusClips is unable to perform filesystem operations natively. Please provide FTP credentials.");
        }

        // Connect to FTP host
        if (static::$_ftp_ssl) {
            if (!function_exists ('ftp_ssl_connect')) throw new Exception("Your host doesn't support FTP over SSL connections.");
            static::$_ftp_stream = @ftp_ssl_connect(static::$_ftp_hostname);
        } else {
            static::$_ftp_stream = @ftp_connect(static::$_ftp_hostname);
        }
        if (!static::$_ftp_stream) throw new Exception("Unable to connect to FTP host (" . static::$_ftp_hostname . ")");

        // Login with username and password
        if (!ftp_login(static::$_ftp_stream, static::$_ftp_username, static::$_ftp_password)) {
            throw new Exception("Unable to login to FTP server (Username: '" . static::$_ftp_username . "', Password: '" . static::$_ftp_password. "')");
        }
    }
}
