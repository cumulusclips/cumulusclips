<?php

class PrivacyMapper
{
    public function getPrivacyById($privacyId)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'privacy WHERE privacyId = :privacyId';
        $dbResults = $db->fetchRow($query, array(':privacyId' => $privacyId));
        if ($db->rowCount() == 1) {
            return $this->_map($dbResults);
        } else {
            return false;
        }
    }

    protected function _map($dbResults)
    {
        $privacy = new Privacy();
        $privacy->privacyId = $dbResults['privacyId'];
        $privacy->userId = $dbResults['userId'];
        $privacy->videoComment = ($dbResults['videoComment'] == 1) ? true : false;
        $privacy->newMessage = ($dbResults['newMessage'] == 1) ? true : false;
        $privacy->newVideo = ($dbResults['newVideo'] == 1) ? true : false;
        $privacy->videoReady = ($dbResults['videoReady'] == 1) ? true : false;
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
            $query .= ' userId = :userId, videoComment = :videoComment, newMessage = :newMessage, newVideo = :newVideo, videoReady = :videoReady';
            $query .= ' WHERE privacyId = :privacyId';
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
            $query .= ' (userId, videoComment, newMessage, newVideo, videoReady)';
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
}