<?php

class FilesystemInstaller
{
    /**
     * @var resource Resource id of FTP connection 
     */
    protected static $_ftp_stream;
    
    /**
     * @var string FTP hostname 
     */
    protected static $_ftp_hostname;
    
    /**
     * @var string FTP username 
     */
    protected static $_ftp_username;
    
    /**
     * @var string FTP password 
     */
    protected static $_ftp_password;
    
    /**
     * @var string Path to CumulusClips from FTP 
     */
    protected static $_ftp_path;
    
    /**
     * @var boolean Flag to use SSL in FTP connection 
     */
    protected static $_ftp_ssl;

    /**
     * Create an empty file, and hierachy too if neccessary
     * @param string $filename Complete path to the file to be created
     * @return boolean Returns true if file is completed
     * @throws Exception If errors are encountered while creating file
     */
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
            $ftp_filename = str_replace(DOC_ROOT, self::$_ftp_path, $filename);
            if (!@ftp_fput(self::$_ftp_stream, $ftp_filename, $stream, FTP_BINARY)) {
                throw new Exception("Unable to create file via FTP ($ftp_filename)");
            }
            fclose($stream);
            self::_close();
        }

        self::setPermissions($filename, 0644);
        return true;
    }

    /**
     * Create a directory, and hierachy too if neccessary
     * @param string $dirname Complete path to new directory to be create
     * @return boolean Return true when directory is successfully created
     * @throws Exception If errors are encountered while creating directory
     */
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
            $ftp_dirname = str_replace(DOC_ROOT, self::$_ftp_path, $dirname);
            if (!@ftp_mkdir(self::$_ftp_stream, $ftp_dirname)) throw new Exception("Unable to create directory via FTP ($ftp_dirname)");
            self::_close();
        }

        self::setPermissions($dirname, 0755);
        return true;
    }

    /**
     * Append content to an existing file
     * @param string $filename Complete path of file to be written to
     * @param string $content Text to be appended to file
     * @return boolean Returns true if file is successfully modified
     * @throws Exception If errors are encountered while writting to file
     */
    public static function write($filename, $content)
    {
        // Perform action directly if able, use FTP otherwise
        if (self::_canUseNative($filename)) {
            if (@file_put_contents($filename, $content, FILE_APPEND) === false) {
                throw new Exception("Unable to write content to file ($filename)");
            }
        } else {
            // Load existing content
            self::_open();
            $stream = tmpfile();
            $ftp_filename = str_replace(DOC_ROOT, self::$_ftp_path, $filename);
            if (!@ftp_fget(self::$_ftp_stream, $stream, $ftp_filename, FTP_BINARY)) {
                throw new Exception("Unable to open file for reading/writing via FTP ($ftp_filename)");
            }

            // Append new content
            fwrite($stream, $content);
            fseek($stream, 0);

            // Save back to file
            $result = @ftp_fput(self::$_ftp_stream, $ftp_filename, $stream, FTP_BINARY);
            if (!$result) {
                throw new Exception("Unable to write content to file via FTP ($ftp_filename)");
            }
            fclose($stream);
            self::_close();
        }
        
        return true;
    }

    /**
     * Copy a file to a new location. Hierachy is created too if neccessary
     * @param string $source Complete path to the original file to be copied
     * @param string $destination Complete path to the final copied file
     * @return boolean Returns true if file is successfully copied
     * @throws Exception If errors are encountered while copying file
     */
    public static function copy($source, $destination)
    {
        // Create folder structure if non-existant
        if (!file_exists(dirname($destination))) self::createDir(dirname($destination));

        // Perform action directly if able, use FTP otherwise
        if (self::_canUseNative($source) && self::_canUseNative(dirname($destination))) {
            if (!@copy($source, $destination)) throw new Exception("Unable to copy file ($source to $destination)");
        } else {
            // Load original content
            self::_open();
            $stream = tmpfile();
            $ftp_filename = str_replace(DOC_ROOT, self::$_ftp_path, $source);
            $ftp_new_filename = str_replace(DOC_ROOT, self::$_ftp_path, $destination);
            if (!@ftp_fget(self::$_ftp_stream, $stream, $ftp_filename, FTP_BINARY)) {
                throw new Exception("Unable to open file for reading/copying via FTP ($ftp_filename)");
            }

            // Overwrite new location
            fseek ($stream, 0);
            if (!@ftp_fput(self::$_ftp_stream, $ftp_new_filename, $stream, FTP_BINARY)) {
                throw new Exception("Unable to copy file via FTP ($ftp_filename to $ftp_new_filename)");
            }
            fclose($stream);
            self::_close();
        }

        self::setPermissions($destination, 0644);
        return true;
    }

    /**
     * Copy a directory and it's content to a new location. Hierachy is created too if neccessary
     * @param string $source Complete path to the original directory to be copied
     * @param string $destination Complete path to the final copied directory
     * @return boolean Returns true if directory is successfully copied
     * @throws Exception If errors are encountered while copying directory
     */
    public static function copyDir($source, $destination)
    {
        // Create folder structure if non-existant
        if (!file_exists(dirname($destination))) self::createDir(dirname($destination));

        // Create empty dst directory
        self::createDir($destination);
        
        // Retrieve directory contents, minus . & ..
        $contents = array_diff(scandir($source), array('.', '..'));
        
        // Check & copy directory contents
        foreach ($contents as $child_item) {
            // Generate new src & dest locations
            $new_src_dirname = $source . '/' . $child_item;
            $new_dst_dirname = $destination . '/' . $child_item;

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

    /**
     * Change the permissions on a file or directory
     * @param string $filename Complete path of the object to be changed
     * @param int $permissions New permissions to be applied to object in octal format, prefix with '0', i.e. 0777
     * @return boolean Returns true if permissions are successfully changed
     * @throws Exception If errors are encountered while changing permissions
     */
    public static function setPermissions($filename, $permissions)
    {
        // Perform action directly if able, use FTP otherwise
        if (self::_canUseNative($filename, true)) {
            if (!@chmod($filename, $permissions)) {
                throw new Exception("Unable to set permissions ($permissions on $filename)");
            }
        } else {
            self::_open();
            $ftp_filename = str_replace(DOC_ROOT, self::$_ftp_path, $filename);
            if (@ftp_chmod(self::$_ftp_stream, $permissions, $ftp_filename) === false) {
                throw new Exception("Unable to set permissions via FTP ($permissions on $ftp_filename)");
            }
            self::_close();
        }
        
        return true;
    }

    /**
     * Rename/Move a file or directory a new location
     * @param string $source Complete path to the original object to be moved
     * @param string $destination Complete path to the final moved object
     * @return boolean Returns true if object is successfully moved
     * @throws Exception If errors are encountered while moving object
     */
    public static function rename($source, $destination)
    {
        // Perform action directly if able, use FTP otherwise
        if (self::_canUseNative($source) && self::_canUseNative(dirname($destination))) {
            if (!rename($source, $destination)) {
                throw new Exception("Unable to rename file ($source to $destination)");
            }
        } else {
            self::_open();
            if (!ftp_rename(self::$_ftp_stream, $source, $destination)) {
                throw new Exception("Unable to rename file via FTP ($source to $destination)");
            }
            self::_close();
        }
        
        return true;
    }
    
    /**
     * Delete a file or directory
     * @param string $filename Complete path of the object to be deleted
     * @return boolean Returns true if object is successfully deleted
     * @throws Exception If errors are encountered while deleting object
     */
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
            if (self::_canUseNative($dirname, true)) {
                if (!@rmdir($dirname)) throw new Exception("Unable to delete directory ($dirname)");
            } else {
                self::_open();
                $ftp_dirname = str_replace(DOC_ROOT, self::$_ftp_path, $dirname);
                if (!@ftp_rmdir(self::$_ftp_stream, $ftp_dirname)) throw new Exception("Unable to delete directory via FTP ($ftp_dirname)");
                self::_close();
            }
        } else {
            // Delete file
            if (self::_canUseNative($filename, true)) {
                if (!@unlink($filename)) throw new Exception("Unable to delete file ($filename)");
            } else {
                self::_open();
                $ftp_filename = str_replace(DOC_ROOT, self::$_ftp_path, $filename);
                if (!@ftp_delete(self::$_ftp_stream, $ftp_filename)) throw new Exception("Unable to delete file via FTP ($ftp_filename)");
                self::_close();
            }
        }
        
        return true;
    }

    /**
     * Extract a zip archive
     * @param string $zipfile Complete path of archive to be extracted
     * @param string $extractTo (optional) Complete path to extract archive to
     * @return boolean Returns true if archive is successfully extracted
     * @throws Exception If errors are encountered while extracting archive
     */
    public static function extract($zipfile, $extractTo = null)
    {
        // Open zip file
        $zip = new ZipArchive();
        if (!$zip->open($zipfile)) throw new Exception("Unable to open zip file ($zipfile)");

        // Extract contents to given location or same dir. if not specified
        $extractTo = ($extractTo) ? $extractTo : dirname($zipfile);
        if (!$zip->extractTo($extractTo)) throw new Exception("Unable to extract zip file ($zipfile to $extractTo)");
        return true;
    }

    /**
     * Determine which type of filesytem functions to use (PHP native vs FTP)
     * @param string $filename Complete path of object to be checked
     * @param boolean $strictCheck (optional) Whether or not to perform a strict
     * check comparing Apache process owner with file owner
     * @return boolean Returns true if native functions can be used, false othewise
     */
    protected static function _canUseNative($filename, $strictCheck = false)
    {
        $native = false;
        if (is_writeable($filename)) {
            if ($strictCheck) {
                $native = (posix_getuid() == fileowner($filename)) ? true : false;
            } else {
                $native = true;
            }
        } else {
            $native = false;
        }
        
        return $native;
    }
    
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

    /**
     * Close any open FTP connections to filesystem 
     */
    protected static function _close()
    {
        @ftp_close(self::$_ftp_stream);
    }
}