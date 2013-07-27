<?php

class FlagMapper
{
    public function getFlagById($flagId)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'flags WHERE flagId = :flagId';
        $dbResults = $db->fetchRow($query, array(':flagId' => $flagId));
        if ($db->rowCount() == 1) {
            return $this->_map($dbResults);
        } else {
            return false;
        }
    }

    protected function _map($dbResults)
    {
        $flag = new Flag();
        $flag->flagId = $dbResults['flagId'];
        $flag->objectId = $dbResults['objectId'];
        $flag->type = $dbResults['type'];
        $flag->userId = $dbResults['userId'];
        $flag->status = $dbResults['status'];
        $flag->dateCreated = date(DATE_FORMAT, strtotime($dbResults['dateCreated']));
        return $flag;
    }

    public function save(Flag $flag)
    {
        $flag = Plugin::triggerFilter('video.beforeSave', $flag);
        $db = Registry::get('db');
        if (!empty($flag->flagId)) {
            // Update
            Plugin::triggerEvent('video.update', $flag);
            $query = 'UPDATE ' . DB_PREFIX . 'flags SET';
            $query .= ' objectId = :objectId, type = :type, userId = :userId, status = :status, dateCreated = :dateCreated';
            $query .= ' WHERE flagId = :flagId';
            $bindParams = array(
                ':flagId' => $flag->flagId,
                ':objectId' => $flag->objectId,
                ':type' => $flag->type,
                ':userId' => $flag->userId,
                ':status' => $flag->status,
                ':dateCreated' => date(DATE_FORMAT, strtotime($flag->dateCreated))
            );
        } else {
            // Create
            Plugin::triggerEvent('video.create', $flag);
            $query = 'INSERT INTO ' . DB_PREFIX . 'flags';
            $query .= ' (objectId, type, userId, status, dateCreated)';
            $query .= ' VALUES (:objectId, :type, :userId, :status, :dateCreated)';
            $bindParams = array(
                ':objectId' => $flag->objectId,
                ':type' => $flag->type,
                ':userId' => $flag->userId,
                ':status' => (!empty($flag->status)) ? $flag->status : 'pending',
                ':dateCreated' => gmdate(DATE_FORMAT)
            );
        }
            
        $db->query($query, $bindParams);
        $flagId = (!empty($flag->flagId)) ? $flag->flagId : $db->lastInsertId();
        Plugin::triggerEvent('video.save', $flagId);
        return $flagId;
    }
}