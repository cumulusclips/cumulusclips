<?php

class Filesystem {
    
    static private $writeable;
    static private $ftp_stream;
    static private $ftp_host;
    static private $ftp_username;
    static private $ftp_password;




    static function Open() {

        self::$writeable = (is_writable (DOC_ROOT)) ? true : false;

        // Login to server via FTP if PHP doesn't have write access
        if (!self::$writeable) {

            // Set FTP login settings
            self::$ftp_host = Settings::Get ('ftp_host');
            self::$ftp_username = Settings::Get ('ftp_username');
            self::$ftp_password = Settings::Get ('ftp_password');

            // Connect to FTP host
            self::$ftp_stream = @ftp_connect (self::$ftp_host);
            if (!self::$ftp_stream) return false;

            // Login with username and password
            return @ftp_login (self::$ftp_stream, self::$ftp_username, self::$ftp_password);
            
        }
    }




    static function Close() {
        ftp_close (self::$ftp_stream);
    }




    static function Delete ($filename) {

        // Perform action directly if able, use FTP otherwise
        if (self::$writeable) {
            return unlink ($filename);
        } else {
            return ftp_delete (self::$ftp_stream, $filename);
        }

    }




    static function Create ($filename, $content) {

        // Perform action directly if able, use FTP otherwise
        if (self::$writeable) {
            return file_put_contents ($filename, $content);
        } else {
            $stream = tmpfile();
            fwrite ($stream, $content);
            $result = ftp_fput (self::$ftp_stream, $filename, $stream, FTP_BINARY);
            fclose ($stream);
            return $result;
        }

    }




    static function CreateDir ($dirname) {

        // Perform action directly if able, use FTP otherwise
        if (self::$writeable) {
            $result = mkdir ($dirname);
        } else {
            $result = ftp_mkdir (self::$ftp_stream, $dirname);
        }
        return ($result) ? true : false;

    }




    static function SetPermissions ($filename, $permissions) {

        // Perform action directly if able, use FTP otherwise
        if (self::$writeable) {
            return chmod ($filename, $permissions);
        } else {
            return ftp_chmod (self::$ftp_stream, $permissions, $filename);
        }

    }




    static function Rename ($old_filename, $new_filename) {

        // Perform action directly if able, use FTP otherwise
        if (self::$writeable) {
            return rename ($old_filename, $new_filename);
        } else {
            return ftp_rename (self::$ftp_stream, $old_filename, $new_filename);
        }

    }

}

?>