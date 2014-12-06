<?php

class MessageMapper extends MapperAbstract
{
    public function getMessageById($messageId)
    {
        return $this->getMessageByCustom(array('message_id' => $messageId));
    }
    
    public function getMessageByCustom(array $params)
    {
        $db = Registry::get('db');
        $query = 'SELECT messages.*, senders.username, recipients.username as recipient_username '
            . 'FROM ' . DB_PREFIX . 'messages AS messages '
            . 'INNER JOIN ' . DB_PREFIX . 'users AS senders ON messages.user_id = senders.user_id '
            . 'INNER JOIN ' . DB_PREFIX . 'users AS recipients ON messages.recipient = recipients.user_id '
            . 'WHERE ';
        
        $queryParams = array();
        foreach ($params as $fieldName => $value) {
            $query .= "messages.$fieldName = :$fieldName AND ";
            $queryParams[":$fieldName"] = $value;
        }
        $query = preg_replace('/\sAND\s$/', '', $query);
        
        $dbResults = $db->fetchRow($query, $queryParams);
        if ($db->rowCount() > 0) {
            return $this->_map($dbResults);
        } else {
            return false;
        }
    }
    
    public function getMultipleMessagesByCustom(array $params)
    {
        $db = Registry::get('db');
        $query = 'SELECT messages.*, senders.username, recipients.username as recipient_username '
            . 'FROM ' . DB_PREFIX . 'messages AS messages '
            . 'INNER JOIN ' . DB_PREFIX . 'users AS senders ON messages.user_id = senders.user_id '
            . 'INNER JOIN ' . DB_PREFIX . 'users AS recipients ON messages.recipient = recipients.user_id '
            . 'WHERE ';
        
        $queryParams = array();
        foreach ($params as $fieldName => $value) {
            $query .= "messages.$fieldName = :$fieldName AND ";
            $queryParams[":$fieldName"] = $value;
        }
        $query = preg_replace('/\sAND\s$/', '', $query);
        
        $dbResults = $db->fetchAll($query, $queryParams);
        
        $recordList = array();
        foreach ($dbResults as $dbRecord) {
            $recordList[] = $this->_map($dbRecord);
        }
        return $recordList;
    }

    protected function _map($dbResults)
    {
        $message = new Message();
        $message->messageId = $dbResults['message_id'];
        $message->userId = $dbResults['user_id'];
        $message->username = $dbResults['username'];
        $message->recipient = $dbResults['recipient'];
        $message->recipientUsername = $dbResults['recipient_username'];
        $message->subject = $dbResults['subject'];
        $message->message = $dbResults['message'];
        $message->status = $dbResults['status'];
        $message->dateCreated = date(DATE_FORMAT, strtotime($dbResults['date_created']));
        return $message;
    }

    public function save(Message $message)
    {
        $db = Registry::get('db');
        if (!empty($message->messageId)) {
            // Update
            $query = 'UPDATE ' . DB_PREFIX . 'messages SET';
            $query .= ' user_id = :userId, recipient = :recipient, subject = :subject, message = :message, status = :status, date_created = :dateCreated';
            $query .= ' WHERE message_id = :messageId';
            $bindParams = array(
                ':messageId' => $message->messageId,
                ':userId' => $message->userId,
                ':recipient' => $message->recipient,
                ':subject' => $message->subject,
                ':message' => $message->message,
                ':status' => (!empty($message->status)) ? $message->status : 'unread',
                ':dateCreated' => date(DATE_FORMAT, strtotime($message->dateCreated))
            );
        } else {
            // Create
            $query = 'INSERT INTO ' . DB_PREFIX . 'messages';
            $query .= ' (user_id, recipient, subject, message, status, date_created)';
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
        return $messageId;
    }
    
    public function getMessagesFromList(array $messageIds)
    {
        $messageList = array();
        if (empty($messageIds)) return $messageList;
        
        $db = Registry::get('db');
        $inQuery = implode(',', array_fill(0, count($messageIds), '?'));
        $sql = 'SELECT messages.*, senders.username, recipients.username as recipient_username '
            . 'FROM ' . DB_PREFIX . 'messages AS messages '
            . 'INNER JOIN ' . DB_PREFIX . 'users AS senders ON messages.user_id = senders.user_id '
            . 'INNER JOIN ' . DB_PREFIX . 'users AS recipients ON messages.recipient = recipients.user_id '
            . 'WHERE message_id IN (' . $inQuery . ')';
        $result = $db->fetchAll($sql, $messageIds);

        foreach($result as $messageRecord) {
            $messageList[] = $this->_map($messageRecord);
        }
        return $messageList;
    }
    
    /**
     * Delete a message
     * @param integer $messagId ID of message to be deleted
     * @return void Record is deleted from database
     */
    public function delete($messageId)
    {
        $db = Registry::get('db');
        $query = 'DELETE FROM ' . DB_PREFIX . 'messages WHERE message_id = :messageId';
        $db->query($query, array(':messageId' => $messageId));
    }
}