<?php

class Filesystem {

    static public $native;
    static private $ftp_stream;
    static private $ftp_hostname;
    static private $ftp_username;
    static private $ftp_password;
    static private $ftp_protocol;



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
            self::$ftp_protocol = $settings->ftp_protocol;

            // Connect to FTP host
            if (self::$ftp_protocol == 'ftp') {
                self::$ftp_stream = @ftp_connect (self::$ftp_hostname);
            } else {
                if (!function_exists('ftp_ssl_connect')) throw new Exception ("Your host doesn't support FTP over SSL connections.");
                self::$ftp_stream = @ftp_ssl_connect (self::$ftp_hostname);
            }
            if (!self::$ftp_stream) throw new Exception ("Unable to connect to FTP host (" . self::$ftp_hostname . ")");



            // Login with username and password
            if (!@ftp_login (self::$ftp_stream, self::$ftp_username, self::$ftp_password)) {
                throw new Exception ("Unable to login to FTP server (Username: '" . self::$ftp_username . "', Password: '" . self::$ftp_password. "')");
            }

        }

        return (self::$native) ? 'native' : 'ftp';

    }




    static function Close() {
        if (!self::$native) @ftp_close (self::$ftp_stream);
    }




    static function Delete ($filename) {

        // If dir. delete contents then dir., if file simply delete
        if (is_dir ($filename)) {

            // Strip trailing slash
            $filename = rtrim ($filename, '/');

            // Delete directory contents recursively
            $contents = array_diff (scandir ($filename), array ('.', '..'));
            foreach ($contents as $file) {
                self::Delete ($filename . '/' . $file);
            }

            // Delete directory
            if (self::CanUseNative ($filename)) {
                if (!@rmdir ($filename)) throw new Exception ("Unable to delete directory ($filename)");
            } else {
                if (!@ftp_rmdir (self::$ftp_stream, $filename)) throw new Exception ("Unable to delete directory via FTP ($filename)");
            }

        } else {

            // Delete file
            if (self::CanUseNative ($filename)) {
                if (!@unlink ($filename)) throw new Exception ("Unable to delete file ($filename)");
            } else {
                if (!@ftp_delete (self::$ftp_stream, $filename)) throw new Exception ("Unable to delete file via FTP ($filename)");
            }

        }

        return true;

    }




    static function Create ($filename) {

        // Create folder structure if non-existant
        if (!file_exists (dirname ($filename))) self::CreateDir (dirname ($filename));

        // Perform action directly if able, use FTP otherwise
        if (self::$native) {
            if (@file_put_contents ($filename, '') === false) throw new Exception ("Unable to create file ($filename)");
        } else {

            $stream = tmpfile();
            if (!@ftp_fput (self::$ftp_stream, $filename, $stream, FTP_BINARY)) {
                throw new Exception ("Unable to create file via FTP ($filename)");
            }
            fclose ($stream);

        }

        self::SetPermissions ($filename, 0644);
        return true;

    }




    static function CreateDir ($dirname) {

        // Create folder structure if non-existant
        if (!file_exists (dirname ($dirname))) self::CreateDir (dirname ($dirname));

        // If dir exists, just update permissions
        if (file_exists ($dirname)) return self::SetPermissions ($dirname, 0755);

        // Perform action directly if able, use FTP otherwise
        if (self::$native) {
            if (!@mkdir ($dirname)) throw new Exception ("Unable to create directory ($dirname)");
        } else {
            if (!@ftp_mkdir (self::$ftp_stream, $dirname)) throw new Exception ("Unable to create directory via FTP ($dirname)");
        }

        self::SetPermissions ($dirname, 0755);
        return true;

    }




    static function Write ($filename, $content) {

        // Perform action directly if able, use FTP otherwise
        if (self::$native) {

            $current_content = @file_get_contents ($filename, $content);
            if (@file_put_contents ($filename, $current_content . $content) === false) {
                throw new Exception ("Unable to write content to file ($filename)");
            }

        } else {

            // Load existing content
            $stream = tmpfile();
            if (!@ftp_fget (self::$ftp_stream, $stream, $filename, FTP_BINARY)) {
                throw new Exception ("Unable to open file for reading/writing via FTP ($filename)");
            }

            // Append new content
            fwrite ($stream, $content);
            fseek ($stream, 0);

            // Save back to file
            $result = @ftp_fput (self::$ftp_stream, $filename, $stream, FTP_BINARY);
            if (!$result) {
                throw new Exception ("Unable to write content to file via FTP ($filename)");
            }
            fclose ($stream);

        }

        return true;

    }




    static function Copy ($filename, $new_filename) {

        // Create folder structure if non-existant
        if (!file_exists (dirname ($new_filename))) self::CreateDir (dirname ($new_filename));

        // Perform action directly if able, use FTP otherwise
        if (self::$native) {
            if (!@copy ($filename, $new_filename)) throw new Exception ("Unable to copy file ($filename to $new_filename)");
        } else {

            // Load original content
            $stream = tmpfile();
            if (!@ftp_fget (self::$ftp_stream, $stream, $filename, FTP_BINARY)) {
                throw new Exception ("Unable to open file for reading/copying via FTP ($filename)");
            }

            // Overwrite new location
            fseek ($stream, 0);
            if (!@ftp_fput (self::$ftp_stream, $new_filename, $stream, FTP_BINARY)) {
                throw new Exception ("Unable to copy file via FTP ($filename to $new_filename)");
            }
            fclose ($stream);
            self::SetPermissions ($new_filename, 0644);

        }

        return true;

    }




    static function CopyDir ($src_dirname, $dst_dirname) {

        // Retrieve directory contents, minus . & ..
        $contents = array_diff (scandir ($src_dirname), array ('.', '..'));

        // Simply create dir if src dir is empty
        if (empty ($contents))  self::CreateDir ($dst_dirname);

        // Check & copy directory contents
        foreach ($contents as $child_item) {

            // Generate new src & dest locations
            $new_src_dirname = $src_dirname . '/' . $child_item;
            $new_dst_dirname = $dst_dirname . '/' . $child_item;

            if (is_dir ($new_src_dirname)) {
                // Copy directory recursively
                self::CopyDir ($new_src_dirname, $new_dst_dirname);
            } else {
                // Copy file
                self::Copy ($new_src_dirname, $new_dst_dirname);
            }

        }

        return true;

    }




    static function SetPermissions ($filename, $permissions) {

        // Perform action directly if able, use FTP otherwise
        if (self::CanUseNative ($filename)) {
            if (!@chmod ($filename, $permissions)) {
                throw new Exception ("Unable to set permissions ($permissions on $filename)");
            }
        } else {
            if (@ftp_chmod (self::$ftp_stream, $permissions, $filename) === false) {
                throw new Exception ("Unable to set permissions via FTP ($permissions on $filename)");
            }
        }
        return true;

    }




    static function CanUseNative ($filename) {
        return (self::$native || (is_writable($filename) && fileowner ($filename) != fileowner (DOC_ROOT)));
    }

}

?>