<?php

class AttachmentMapper extends MapperAbstract
{
    /**
     * @var TABLE Main source table
     */
    const TABLE = 'attachments';

    /**
     * @var KEY Source table's key field
     */
    const KEY = 'attachment_id';

    /**
     * Maps the values from an attachment record to the properties in a attachment data model
     *
     * @param array $record The record from the attachment table
     * @return Attachment Returns an instance of a Attachment data model populated with the record's data
     */
    protected function _map($record)
    {
        $attachment = new Attachment();
        $attachment->attachmentId = (int) $record['attachment_id'];
        $attachment->fileId = (int) $record['file_id'];
        $attachment->videoId = (int) $record['video_id'];
        $attachment->dateCreated = new \DateTime($record['date_created'], new \DateTimeZone('UTC'));
        return $attachment;
    }

    /**
     * Creates or updates an attachment entry in the database. New entry is created if no id is provided
     *
     * @param Attachment $attachment The attachment being saved
     * @return int Returns the id of the saved attachment entry
     */
    public function save(Attachment $attachment)
    {
        $db = Registry::get('db');

        if (!empty($attachment->attachmentId)) {

            // Update
            $query = 'UPDATE ' . DB_PREFIX . static::TABLE . ' SET';
            $query .= ' file_id = :fileId, video_id = :videoId, date_created = :dateCreated';
            $query .= ' WHERE attachment_id = :attachmentId';
            $bindParams = array(
                ':attachmentId' => $attachment->attachmentId,
                ':file_id' => $attachment->fileId,
                ':video_id' => $attachment->videoId,
                ':dateCreated' => $attachment->dateCreated->format(DATE_FORMAT)
            );
        } else {

            // Create
            $query = 'INSERT INTO ' . DB_PREFIX . static::TABLE;
            $query .= ' (file_id, video_id, date_created)';
            $query .= ' VALUES (:fileId, :videoId, :dateCreated)';
            $bindParams = array(
                ':fileId' => $attachment->fileId,
                ':videoId' => $attachment->videoId,
                ':dateCreated' => gmdate(DATE_FORMAT)
            );
        }

        $db->query($query, $bindParams);
        $attachmentId = (!empty($attachment->attachmentId)) ? $attachment->attachmentId : $db->lastInsertId();
        return $attachmentId;
    }
}