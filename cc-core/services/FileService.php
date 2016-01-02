<?php

class FileService extends ServiceAbstract
{
    /**
     * Retrieve URL to given file
     * @param File Instance of file who's URL is being retrieved
     * @return string Returns URL to file
     */
    public function getUrl(File $file)
    {
        return HOST . "/cc-content/uploads/files/$file->filename.$file->extension";
    } 
    
    /**
     * Delete a file
     * @param File $file Instance of file to be deleted
     * @return void File is deleted from database and the filesystem
     */
    public function delete(File $file)
    {
        // Delete file record
        $fileMapper = $this->_getMapper();
        $fileMapper->delete($file->fileId);
        
        // Delete file from the filesystem
        try {
            Filesystem::delete(UPLOAD_PATH . '/files/' . $file->filename . '.' . $file->extension);
        } catch (Exception $e) {
            App::alert('Error During File Removal', "Unable to delete file for: $file->filename. The file has been removed from the database, but the file still remains on the filesystem. Error: " . $e->getMessage());
        }
    }

    /**
     * Generate a unique random string for a file filename
     * @return string Random file filename
     */
    public function generateFilename()
    {
        $fileMapper = $this->_getMapper();
        $filenameAvailable = null;
        do {
            $filename = Functions::random(20);
            if (!$fileMapper->getByFilename($filename)) $filenameAvailable = true;
        } while (empty($filenameAvailable));
        return $filename;
    }

    /**
     * Retrieves the user who uploaded a file
     * @param File $file The file whose user is being retrieved
     * @return User Returns the user instance who uploaded the file
     */
    public function getUser(File $file)
    {
        $userMapper = new UserMapper();
        return $userMapper->getUserById($file->userId);
    }

    /**
     * Retrieve instance of File mapper
     * @return FileMapper Mapper is returned
     */
    protected function _getMapper()
    {
        return new FileMapper();
    }
}