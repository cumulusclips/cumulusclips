<?php

class FileMapper extends MapperAbstract
{
    /**
     * Main source table
     */
    const TABLE = 'files';

    /**
     * Source table's key field
     */
    const KEY = 'file_id';

    /**
     * Retrieve a file by it's id
     * @param string $filename Filename of the file being retrieved
     * @return File Returns the matching file
     */
    public function getByFilename($filename)
    {
        return $this->getByCustom(array('filename' => $filename));
    }

    /**
     * Retrieves all the files by a given user
     * @params int $userId The id of the user whose files are being retrieved
     * @return File[] Returns all files by the user
     */
    public function getUserFiles($userId)
    {
        return $this->getMultipleByCustom(array('user_id' => $userId));
    }

    /**
     * Maps the values from a file record to the properties in a file data model
     * @param array $record The record from the files table
     * @return File Returns an instance of a File data model populated with the record's data
     */
    protected function _map($record)
    {
        $file = new File();
        $file->fileId = (int) $record['file_id'];
        $file->filename = $record['filename'];
        $file->userId = (int) $record['user_id'];
        $file->title = $record['title'];
        $file->description = $record['description'];
        $file->filesize = (int) $record['filesize'];
        $file->extension = $record['extension'];
        $file->attachable = ($record['attachable'] == 1) ? true : false;
        $file->dateCreated = date(DATE_FORMAT, strtotime($record['date_created']));
        return $file;
    }

    /**
     * Creates or updates a file record in the database. New record is created if no id is provided.
     * @param File $file The file being saved
     * @return int Returns the id of the saved file record
     */
    public function save(File $file)
    {
        $db = Registry::get('db');
        if (!empty($file->fileId)) {
            // Update
            $query = 'UPDATE ' . DB_PREFIX . static::TABLE . ' SET';
            $query .= ' filename = :filename, user_id = :userId, title = :title, description = :description, filesize = :filesize, extension = :extension, attachable = :attachable, date_created = :dateCreated';
            $query .= ' WHERE file_id = :fileId';
            $bindParams = array(
                ':fileId' => $file->fileId,
                ':filename' => $file->filename,
                ':userId' => $file->userId,
                ':title' => $file->title,
                ':description' => (!empty($file->description)) ? $file->description : null,
                ':filesize' => ($file->filesize < 1) ? 1 : $file->filesize,
                ':extension' => $file->extension,
                ':attachable' => (isset($file->attachable) && $file->attachable === true) ? 1 : 0,
                ':dateCreated' => date(DATE_FORMAT, strtotime($file->dateCreated))
            );
        } else {
            // Create
            $query = 'INSERT INTO ' . DB_PREFIX . static::TABLE;
            $query .= ' (filename, user_id, title, description, filesize, extension, attachable, date_created)';
            $query .= ' VALUES (:filename, :userId, :title, :description, :filesize, :extension, :attachable, :dateCreated)';
            $bindParams = array(
                ':filename' => $file->filename,
                ':userId' => $file->userId,
                ':title' => $file->title,
                ':description' => (!empty($file->description)) ? $file->description : null,
                ':filesize' => ($file->filesize < 1) ? 1 : $file->filesize,
                ':extension' => $file->extension,
                ':attachable' => (isset($file->attachable) && $file->attachable === true) ? 1 : 0,
                ':dateCreated' => gmdate(DATE_FORMAT)
            );
        }

        $db->query($query, $bindParams);
        $fileId = (!empty($file->fileId)) ? $file->fileId : $db->lastInsertId();
        return $fileId;
    }
}