<?php

class FileMapper extends MapperAbstract
{
    /**
     * @var string Attachment file type
     */
    const TYPE_ATTACHMENT = 'attachment';

     /**
     * @var string Library file type
     */
    const TYPE_LIBRARY = 'library';

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
        $file->type = $record['type'];
        $file->userId = (int) $record['user_id'];
        $file->name = $record['name'];
        $file->filesize = (int) $record['filesize'];
        $file->extension = $record['extension'];
        $file->dateCreated = new \DateTime($record['date_created'], new \DateTimeZone('UTC'));
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
            $query .= ' filename = :filename, type = :type, user_id = :userId, name = :name, filesize = :filesize, extension = :extension, date_created = :dateCreated';
            $query .= ' WHERE file_id = :fileId';
            $bindParams = array(
                ':fileId' => $file->fileId,
                ':filename' => $file->filename,
                ':type' => $file->type,
                ':userId' => $file->userId,
                ':name' => $file->name,
                ':filesize' => ($file->filesize < 1) ? 1 : $file->filesize,
                ':extension' => $file->extension,
                ':dateCreated' => $file->dateCreated->format(DATE_FORMAT)
            );
        } else {
            // Create
            $query = 'INSERT INTO ' . DB_PREFIX . static::TABLE;
            $query .= ' (filename, type, user_id, name, filesize, extension, date_created)';
            $query .= ' VALUES (:filename, :type, :userId, :name, :filesize, :extension, :dateCreated)';
            $bindParams = array(
                ':filename' => $file->filename,
                ':type' => $file->type,
                ':userId' => $file->userId,
                ':name' => $file->name,
                ':filesize' => ($file->filesize < 1) ? 1 : $file->filesize,
                ':extension' => $file->extension,
                ':dateCreated' => gmdate(DATE_FORMAT)
            );
        }

        $db->query($query, $bindParams);
        $fileId = (!empty($file->fileId)) ? $file->fileId : $db->lastInsertId();
        return $fileId;
    }
}