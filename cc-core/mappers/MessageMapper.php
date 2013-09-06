<?php

class MessageMapper
{
    public function getMessageById($messageId)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'messages WHERE messageId = :messageId';
        $dbResults = $db->fetchRow($query, array(':messageId' => $messageId));
        if ($db->rowCount() == 1) {
            return $this->_map($dbResults);
        } else {
            return false;
        }
    }

    protected function _map($dbResults)
    {
        $message = new Message();
        $message->messageId = $dbResults['messageId'];
        $message->userId = $dbResults['userId'];
        $message->recipient = $dbResults['recipient'];
        $message->subject = $dbResults['subject'];
        $message->message = $dbResults['message'];
        $message->status = $dbResults['status'];
        $message->dateCreated = date(DATE_FORMAT, strtotime($dbResults['dateCreated']));
        return $message;
    }

    public function save(Message $message)
    {
        $message = Plugin::triggerFilter('video.beforeSave', $message);
        $db = Registry::get('db');
        if (!empty($message->messageId)) {
            // Update
            Plugin::triggerEvent('video.update', $message);
            $query = 'UPDATE ' . DB_PREFIX . 'messages SET';
            $query .= ' userId = :userId, recipient = :recipient, subject = :subject, message = :message, status = :status, dateCreated = :dateCreated';
            $query .= ' WHERE messageId = :messageId';
            $bindParams = array(
                ':messageId' => $message->messageId,
                ':userId' => $message->userId,
                ':recipient' => $message->recipient,
                ':subject' => $message->subject,
                ':message' => $message->message,
                ':status' => $message->status,
                ':dateCreated' => date(DATE_FORMAT, strtotime($message->dateCreated))
            );
        } else {
            // Create
            Plugin::triggerEvent('video.create', $message);
            $query = 'INSERT INTO ' . DB_PREFIX . 'messages';
            $query .= ' (userId, recipient, subject, message, status, dateCreated)';
            $query .= ' VALUES (:userId, :recipient, :subject, :message, :status, :dateCreated)';
            $bindParams = array(
                ':userId' => $message->userId,
                ':recipient' => $message->recipient,
                ':subject' => $message->subject,
                ':message' => $message->message,
                ':status' => (!empty($message->status)) ? $message->status : 'unread',
                ':dateCreated' => gmdate(DATE_FORMAT)
            );
        }
            
        $db->query($query, $bindParams);
        $messageId = (!empty($message->messageId)) ? $message->messageId : $db->lastInsertId();
        Plugin::triggerEvent('video.save', $messageId);
        return $messageId;
    }
}