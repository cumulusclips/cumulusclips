<?php

class Filesystem
{
    static protected $ftp_stream;
    static protected $ftp_hostname;
    static protected $ftp_username;
    static protected $ftp_password;
    static protected $ftp_path;
    static protected $ftp_ssl;

    public static function create($filename)
    {
        // Create folder structure if non-existant
        if (!file_exists(dirname($filename))) self::createDir(dirname($filename));

        // Perform action directly if able, use FTP otherwise
        if (self::_canUseNative(dirname($filename))) {
            if (@file_put_contents($filename, '') === false) throw new Exception("Unable to create file ($filename)");
        } else {
            self::_open();
            $stream = tmpfile();
            $ftp_filename = str_replace(DOC_ROOT, self::$ftp_path, $filename);
            if (!@ftp_fput(self::$ftp_stream, $ftp_filename, $stream, FTP_BINARY)) {
                throw new Exception("Unable to create file via FTP ($ftp_filename)");
            }
            fclose($stream);
            self::_close();
        }

        self::setPermissions($filename, 0644);
        return true;
    }

    public static function createDir($dirname)
    {
        // Create folder structure if non-existant
        if (!file_exists(dirname($dirname))) self::createDir(dirname($dirname));

        // If dir exists, just update permissions
        if (file_exists($dirname)) return self::setPermissions($dirname, 0755);

        // Perform action directly if able, use FTP otherwise
        if (self::_canUseNative(dirname($dirname))) {
            if (!@mkdir($dirname)) throw new Exception("Unable to create directory ($dirname)");
        } else {
            self::_open();
            $ftp_dirname = str_replace(DOC_ROOT, self::$ftp_path, $dirname);
            if (!@ftp_mkdir(self::$ftp_stream, $ftp_dirname)) throw new Exception("Unable to create directory via FTP ($ftp_dirname)");
            self::_close();
        }

        self::setPermissions($dirname, 0755);
        return true;
    }

    public static function write($filename, $content)
    {
        // Perform action directly if able, use FTP otherwise
        if (self::_canUseNative($filename)) {
            $current_content = @file_get_contents($filename, $content);
            if (@file_put_contents($filename, $current_content . $content) === false) {
                throw new Exception("Unable to write content to file ($filename)");
            }
        } else {
            // Load existing content
            self::_open();
            $stream = tmpfile();
            $ftp_filename = str_replace(DOC_ROOT, self::$ftp_path, $filename);
            if (!@ftp_fget(self::$ftp_stream, $stream, $ftp_filename, FTP_BINARY)) {
                throw new Exception("Unable to open file for reading/writing via FTP ($ftp_filename)");
            }

            // Append new content
            fwrite($stream, $content);
            fseek($stream, 0);

            // Save back to file
            $result = @ftp_fput(self::$ftp_stream, $ftp_filename, $stream, FTP_BINARY);
            if (!$result) {
                throw new Exception("Unable to write content to file via FTP ($ftp_filename)");
            }
            fclose($stream);
            self::_close();
        }
        
        return true;
    }

    public static function copy($filename, $new_filename)
    {
        // Create folder structure if non-existant
        if (!file_exists(dirname($new_filename))) self::createDir(dirname($new_filename));

        // Perform action directly if able, use FTP otherwise
        if (self::_canUseNative($filename) && self::_canUseNative(dirname($new_filename))) {
            if (!@copy($filename, $new_filename)) throw new Exception("Unable to copy file ($filename to $new_filename)");
        } else {
            // Load original content
            self::_open();
            $stream = tmpfile();
            $ftp_filename = str_replace(DOC_ROOT, self::$ftp_path, $filename);
            $ftp_new_filename = str_replace(DOC_ROOT, self::$ftp_path, $new_filename);
            if (!@ftp_fget(self::$ftp_stream, $stream, $ftp_filename, FTP_BINARY)) {
                throw new Exception("Unable to open file for reading/copying via FTP ($ftp_filename)");
            }

            // Overwrite new location
            fseek ($stream, 0);
            if (!@ftp_fput(self::$ftp_stream, $ftp_new_filename, $stream, FTP_BINARY)) {
                throw new Exception("Unable to copy file via FTP ($ftp_filename to $ftp_new_filename)");
            }
            fclose($stream);
            self::_close();
        }

        self::setPermissions($new_filename, 0644);
        return true;
    }

    public static function copyDir($src_dirname, $dst_dirname)
    {
        // Create folder structure if non-existant
        if (!file_exists(dirname($dst_dirname))) self::createDir(dirname($dst_dirname));

        // Create empty dst directory
        self::createDir($dst_dirname);
        
        // Retrieve directory contents, minus . & ..
        $contents = array_diff(scandir($src_dirname), array('.', '..'));
        
        // Check & copy directory contents
        foreach ($contents as $child_item) {
            // Generate new src & dest locations
            $new_src_dirname = $src_dirname . '/' . $child_item;
            $new_dst_dirname = $dst_dirname . '/' . $child_item;

            if (is_dir($new_src_dirname)) {
                // Copy directory recursively
                self::copyDir($new_src_dirname, $new_dst_dirname);
            } else {
                // Copy file
                self::copy($new_src_dirname, $new_dst_dirname);
            }
        }
        
        return true;
    }

    public static function setPermissions($filename, $permissions)
    {
        // Perform action directly if able, use FTP otherwise
        if (self::_canUseNative($filename, true)) {
            if (!@chmod($filename, $permissions)) {
                throw new Exception("Unable to set permissions ($permissions on $filename)");
            }
        } else {
            self::_open();
            $ftp_filename = str_replace(DOC_ROOT, self::$ftp_path, $filename);
            if (@ftp_chmod(self::$ftp_stream, $permissions, $ftp_filename) === false) {
                throw new Exception("Unable to set permissions via FTP ($permissions on $ftp_filename)");
            }
            self::_close();
        }
        
        return true;
    }

    public static function rename($old_filename, $new_filename)
    {
        // Perform action directly if able, use FTP otherwise
        if (self::_canUseNative($filename) && self::_canUseNative(dirname($new_filename))) {
            if (!rename($old_filename, $new_filename)) {
                throw new Exception("Unable to rename file ($old_filename to $new_filename)");
            }
        } else {
            self::_open();
            if (!ftp_rename(self::$ftp_stream, $old_filename, $new_filename)) {
                throw new Exception("Unable to rename file via FTP ($old_filename to $new_filename)");
            }
            self::_close();
        }
        
        return true;
    }
    
    public static function delete($filename)
    {
        // If dir. delete contents then dir., if file simply delete
        if (is_dir($filename)) {

            // Strip trailing slash
            $dirname = rtrim($filename, '/');

            // Delete directory contents recursively
            $contents = array_diff(scandir($dirname), array('.', '..'));
            foreach ($contents as $file) {
                self::delete($dirname . '/' . $file);
            }

            // Delete directory
            if (self::_canUseNative($dirname)) {
                if (!@rmdir($dirname)) throw new Exception("Unable to delete directory ($dirname)");
            } else {
                self::_open();
                $ftp_dirname = str_replace(DOC_ROOT, self::$ftp_path, $dirname);
                if (!@ftp_rmdir(self::$ftp_stream, $ftp_dirname)) throw new Exception("Unable to delete directory via FTP ($ftp_dirname)");
                self::_close();
            }
        } else {
            // Delete file
            if (self::_canUseNative($filename)) {
                if (!@unlink($filename)) throw new Exception("Unable to delete file ($filename)");
            } else {
                self::_open();
                $ftp_filename = str_replace(DOC_ROOT, self::$ftp_path, $filename);
                if (!@ftp_delete(self::$ftp_stream, $ftp_filename)) throw new Exception("Unable to delete file via FTP ($ftp_filename)");
                self::_close();
            }
        }
        
        return true;
    }

    public static function extract($zipfile, $extract_to = null)
    {
        // Open zip file
        $zip = new ZipArchive();
        if (!$zip->open($zipfile)) throw new Exception("Unable to open zip file ($zipfile)");

        // Extract contents to given location or same dir. if not specified
        $extract_to = ($extract_to) ? $extract_to : dirname($zipfile);
        if (!$zip->extractTo($extract_to)) throw new Exception("Unable to extract zip file ($zipfile to $extract_to)");
        return true;
    }

    protected static function _canUseNative($filename, $strictCheck = false)
    {
        $native = false;
        if (is_writeable($filename)) {
            if ($strictCheck) {
                if (posix_getuid() == fileowner($filename)) {
                    $native = true;
                } else {
                    $native = false;
                }
            } else {
                $native = true;
            }
        } else {
            $native = false;
        }
        
        return $native;
    }
    
    protected static function _open()
    {
        // Set FTP login settings
        self::$ftp_hostname = FTP_HOST;
        self::$ftp_username = FTP_USER;
        self::$ftp_password = FTP_PASS;
        self::$ftp_path = FTP_PATH;
        self::$ftp_ssl = FTP_SSL;

        // Connect to FTP host
        if (self::$ftp_ssl) {
            if (!function_exists ('ftp_ssl_connect')) throw new Exception("Your host doesn't support FTP over SSL connections.");
            self::$ftp_stream = @ftp_ssl_connect(self::$ftp_hostname);
        } else {
            self::$ftp_stream = @ftp_connect(self::$ftp_hostname);
        }
        if (!self::$ftp_stream) throw new Exception("Unable to connect to FTP host (" . self::$ftp_hostname . ")");

        // Login with username and password
        if (!ftp_login(self::$ftp_stream, self::$ftp_username, self::$ftp_password)) {
            throw new Exception("Unable to login to FTP server (Username: '" . self::$ftp_username . "', Password: '" . self::$ftp_password. "')");
        }
    }

    protected static function _close()
    {
        @ftp_close(self::$ftp_stream);
    }
}