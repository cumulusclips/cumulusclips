<?php

class Filesystem {
    
    static private $ftp_stream;
    static private $ftp_host = 'localhost';
    static private $ftp_username = 'miguel';
    static private $ftp_password = 'Damian646';




    static function Open() {

        // Connect to FTP host
        $ftp_stream = ftp_connect (self::$ftp_host) or die ('Unable to connect to FTP host');

        // Login with username and password
        $login_result = ftp_login (self::$ftp_stream, self::$ftp_username, self::$ftp_password) or die ('Unable to login to FTP server');
    }




    static function Close() {
        // Close connection
        ftp_close (self::$ftp_stream);
    }




    static function Delete ($filename) {

    }




    static function Create ($filename, $content) {
        if (false) {
            $result = file_put_contents ($filename, $content);
        } else {
            $stream = tmpfile();
            fwrite ($stream, $content);
            $result = ftp_fput (self::$ftp_stream, $filename, $stream, FTP_BINARY);
            ftp_chmod (self::$ftp_stream, 0644, $remote_file);
            fclose ($stream);
        }
        return ($result) ? true : false;
    }




    static function CreateDir ($dirname) {

    }




    static function SetPermissions ($permissions) {

    }




    static function Rename ($old_filename, $new_filename) {

    }

}

?>