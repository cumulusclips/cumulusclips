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
        $query = rtrim($query, ' AND ');
        
        $dbResults = $db->fetchRow($query, $queryParams);
        if ($db->rowCount() == 1) {
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
        return $privacy;
    }

    public function save(Privacy $privacy)
    {
        $privacy = Plugin::triggerFilter('video.beforeSave', $privacy);
        $db = Registry::get('db');
        if (!empty($privacy->privacyId)) {
            // Update
            Plugin::triggerEvent('video.update', $privacy);
            $query = 'UPDATE ' . DB_PREFIX . 'privacy SET';
            $query .= ' user_id = :userId, video_comment = :videoComment, new_message = :newMessage, new_video = :newVideo, video_ready = :videoReady';
            $query .= ' WHERE privacy_id = :privacyId';
            $bindParams = array(
                ':privacyId' => $privacy->privacyId,
                ':userId' => $privacy->userId,
                ':videoComment' => (isset($privacy->videoComment) && $privacy->videoComment === true) ? 1 : 0,
                ':newMessage' => (isset($privacy->newMessage) && $privacy->newMessage === true) ? 1 : 0,
                ':newVideo' => (isset($privacy->newVideo) && $privacy->newVideo === true) ? 1 : 0,
                ':videoReady' => (isset($privacy->videoReady) && $privacy->videoReady === true) ? 1 : 0,
            );
        } else {
            // Create
            Plugin::triggerEvent('video.create', $privacy);
            $query = 'INSERT INTO ' . DB_PREFIX . 'privacy';
            $query .= ' (user_id, video_comment, new_message, new_video, video_ready)';
            $query .= ' VALUES (:userId, :videoComment, :newMessage, :newVideo, :videoReady)';
            $bindParams = array(
                ':userId' => $privacy->userId,
                ':videoComment' => (isset($privacy->videoComment) && $privacy->videoComment === true) ? 1 : 0,
                ':newMessage' => (isset($privacy->newMessage) && $privacy->newMessage === true) ? 1 : 0,
                ':newVideo' => (isset($privacy->newVideo) && $privacy->newVideo === true) ? 1 : 0,
                ':videoReady' => (isset($privacy->videoReady) && $privacy->videoReady === true) ? 1 : 0,
            );
        }
            
        $db->query($query, $bindParams);
        $privacyId = (!empty($privacy->privacyId)) ? $privacy->privacyId : $db->lastInsertId();
        Plugin::triggerEvent('video.save', $privacyId);
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