<?php

class PrivacyMapper extends MapperAbstract
{
    public function getPrivacyById($privacyId)
    {
        return $this->getPrivacyByCustom(array('privacy_id' => $privacyId));
    }
    
    public function getPrivacyByUser($userId)
    {
        return $this->getPrivacyByCustom(array('user_id' => $userId));
    }
    
    public function getPrivacyByCustom(array $params)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'privacy WHERE ';
        $queryParams = array();
        foreach ($params as $fieldName => $value) {
            $query .= "$fieldName = :$fieldName AND ";
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

    protected function _map($dbResults)
    {
        $privacy = new Privacy();
        $privacy->privacyId = $dbResults['privacy_id'];
        $privacy->userId = $dbResults['user_id'];
        $privacy->videoComment = ($dbResults['video_comment'] == 1) ? true : false;
        $privacy->newMessage = ($dbResults['new_message'] == 1) ? true : false;
        $privacy->newVideo = ($dbResults['new_video'] == 1) ? true : false;
        $privacy->videoReady = ($dbResults['video_ready'] == 1) ? true : false;
        $privacy->commentReply = ($dbResults['comment_reply'] == 1) ? true : false;
        return $privacy;
    }

    public function save(Privacy $privacy)
    {
        $db = Registry::get('db');
        if (!empty($privacy->privacyId)) {
            // Update
            $query = 'UPDATE ' . DB_PREFIX . 'privacy SET';
            $query .= ' user_id = :userId, video_comment = :videoComment, new_message = :newMessage, new_video = :newVideo, video_ready = :videoReady, comment_reply = :commentReply';
            $query .= ' WHERE privacy_id = :privacyId';
            $bindParams = array(
                ':privacyId' => $privacy->privacyId,
                ':userId' => $privacy->userId,
                ':videoComment' => (isset($privacy->videoComment) && $privacy->videoComment === true) ? 1 : 0,
                ':newMessage' => (isset($privacy->newMessage) && $privacy->newMessage === true) ? 1 : 0,
                ':newVideo' => (isset($privacy->newVideo) && $privacy->newVideo === true) ? 1 : 0,
                ':videoReady' => (isset($privacy->videoReady) && $privacy->videoReady === true) ? 1 : 0,
                ':commentReply' => (isset($privacy->commentReply) && $privacy->commentReply === true) ? 1 : 0
            );
        } else {
            // Create
            $query = 'INSERT INTO ' . DB_PREFIX . 'privacy';
            $query .= ' (user_id, video_comment, new_message, new_video, video_ready, comment_reply)';
            $query .= ' VALUES (:userId, :videoComment, :newMessage, :newVideo, :videoReady, :commentReply)';
            $bindParams = array(
                ':userId' => $privacy->userId,
                ':videoComment' => (isset($privacy->videoComment) && $privacy->videoComment === true) ? 1 : 0,
                ':newMessage' => (isset($privacy->newMessage) && $privacy->newMessage === true) ? 1 : 0,
                ':newVideo' => (isset($privacy->newVideo) && $privacy->newVideo === true) ? 1 : 0,
                ':videoReady' => (isset($privacy->videoReady) && $privacy->videoReady === true) ? 1 : 0,
                ':commentReply' => (isset($privacy->commentReply) && $privacy->commentReply === true) ? 1 : 0
            );
        }
            
        $db->query($query, $bindParams);
        $privacyId = (!empty($privacy->privacyId)) ? $privacy->privacyId : $db->lastInsertId();
        return $privacyId;
    }
    
    /**
     * Delete a privacy record
     * @param integer $privacyId Id of privacy record to be deleted
     * @return void Record is deleted from system
     */
    public function delete($privacyId)
    {
        $db = Registry::get('db');
        $query = 'DELETE FROM ' . DB_PREFIX . 'privacy WHERE privacy_id = :privacyId';
        $db->query($query, array(':privacyId' => $privacyId));
    }
}