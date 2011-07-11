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

        // Recursively delete file/dir.
        if (is_dir ($filename)) {
            $base = dirname ($filename);
            // Retrieve directory contents, excluding . & ..
            $contents = array_diff (scandir ($filename), array ('.', '..'));
            foreach ($contents as $file) {
                self::Delete ($base . '/' . $file);
            }
            return (self::$writeable) ? rmdir ($filename) : ftp_rmdir (self::$ftp_stream, $filename);
        } else {
            return (self::$writeable) ? unlink ($filename) : ftp_delete (self::$ftp_stream, $filename);
        }

    }




    static function Create ($filename) {

        // Create folder structure if non-existant
        if (!file_exists (dirname ($filename))) self::CreateDir (dirname ($filename));

        // Perform action directly if able, use FTP otherwise
        if (self::$writeable) {
            $result = @file_put_contents ($filename, '');
        } else {
            $stream = tmpfile();
            $result = @ftp_fput (self::$ftp_stream, $filename, $stream, FTP_BINARY);
            fclose ($stream);
        }
        return ($result) ? self::SetPermissions ($filename, 0644) : false;

    }




    static function CreateDir ($dirname) {

        // Create folder structure if non-existant
        if (!file_exists (dirname ($dirname))) self::CreateDir (dirname ($dirname));

        // If dir exists, just update permissions
        if (file_exists ($dirname)) return self::SetPermissions ($dirname, 0755);

        // Perform action directly if able, use FTP otherwise
        if (self::$writeable) {
            $result = mkdir ($dirname);
        } else {
            $result = ftp_mkdir (self::$ftp_stream, $dirname);
        }
        return ($result) ? self::SetPermissions ($dirname, 0755) : false;

    }




    static function Write ($filename, $content) {

        // Perform action directly if able, use FTP otherwise
        if (self::$writeable) {
            $current_content = @file_get_contents ($filename, $content);
            return @file_put_contents ($filename, $current_content . $content);
        } else {

            // Load existing content
            $stream = tmpfile();
            @ftp_fget (self::$ftp_stream, $stream, $filename, FTP_BINARY);

            // Append new content
            fwrite ($stream, $content);
            fseek ($stream, 0);

            // Save back to file
            $result = @ftp_fput (self::$ftp_stream, $filename, $stream, FTP_BINARY);
            fclose ($stream);
            return $result;
        }

    }




    static function Copy ($filename, $new_filename) {

        // Create folder structure if non-existant
        if (!file_exists (dirname ($new_filename))) self::CreateDir (dirname ($new_filename));

        // Perform action directly if able, use FTP otherwise
        if (self::$writeable) {
            $result = copy ($filename, $new_filename);
        } else {

            // Load original content
            $stream = tmpfile();
            @ftp_fget (self::$ftp_stream, $stream, $filename, FTP_BINARY);

            // Overwrite new location
            fseek ($stream, 0);
            $result = ftp_fput (self::$ftp_stream, $new_filename, $stream, FTP_BINARY);
            fclose ($stream);
            return ($result) ? self::SetPermissions ($new_filename, 0644) : false;
        }
    }




    static function CopyDir ($src_dirname, $dst_dirname) {

        // Retrieve directory contents, minus . & ..
        $contents = array_diff (scandir ($src_dirname), array ('.', '..'));

        // Simply create dir if src dir is empty
        if (empty ($contents)) {
            if (!self::CreateDir ($dst_dirname)) return false;
        }

        // Check & copy directory contents
        foreach ($contents as $child_item) {

            // Generate new src & dest locations
            $new_src_dirname = $src_dirname . '/' . $child_item;
            $new_dst_dirname = $dst_dirname . '/' . $child_item;

            if (is_dir ($new_src_dirname)) {
                // Copy directory recursively
                if (!self::CopyDir ($new_src_dirname, $new_dst_dirname)) return false;
            } else {
                // Copy file
                if (!self::Copy ($new_src_dirname, $new_dst_dirname)) return false;
            }

        }

        return true;

    }




    static function SetPermissions ($filename, $permissions) {

        // Perform action directly if able, use FTP otherwise
        if (self::$writeable) {
            return @chmod ($filename, $permissions);
        } else {
            $result = @ftp_chmod (self::$ftp_stream, $permissions, $filename);
            return ($result !== false) ? true : false;
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




    static function Extract ($zipfile, $extract_to = null) {
        $zip = new ZipArchive();
        if (!$zip->open ($zipfile)) return false;
        $extract_to = ($extract_to) ? $extract_to : dirname ($zipfile);
        return $zip->extractTo ($extract_to);
    }

}

?>