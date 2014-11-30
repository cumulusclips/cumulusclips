<?php

class FlagMapper extends MapperAbstract
{
    public function getFlagById($flagId)
    {
        return $this->getFlagByCustom(array('flag_id' => $flagId));
    }

    public function getFlagByCustom(array $params)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'flags WHERE ';
        
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
    
    public function getMultipleFlagsByCustom(array $params)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'flags WHERE ';
        
        $queryParams = array();
        foreach ($params as $fieldName => $value) {
            $query .= "$fieldName = :$fieldName AND ";
            $queryParams[":$fieldName"] = $value;
        }
        $query = preg_replace('/\sAND\s$/', '', $query);
        $dbResults = $db->fetchAll($query, $queryParams);
        
        $flagsList = array();
        foreach($dbResults as $record) {
            $flagsList[] = $this->_map($record);
        }
        return $flagsList;
    }

    protected function _map($dbResults)
    {
        $flag = new Flag();
        $flag->flagId = $dbResults['flag_id'];
        $flag->objectId = $dbResults['object_id'];
        $flag->type = $dbResults['type'];
        $flag->userId = $dbResults['user_id'];
        $flag->status = $dbResults['status'];
        $flag->dateCreated = date(DATE_FORMAT, strtotime($dbResults['date_created']));
        return $flag;
    }

    public function save(Flag $flag)
    {
        $db = Registry::get('db');
        if (!empty($flag->flagId)) {
            // Update
            $query = 'UPDATE ' . DB_PREFIX . 'flags SET';
            $query .= ' object_id = :objectId, type = :type, user_id = :userId, status = :status, date_created = :dateCreated';
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
            $query = 'INSERT INTO ' . DB_PREFIX . 'flags';
            $query .= ' (object_id, type, user_id, status, date_created)';
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
        return $flagId;
    }
    
    
    public function getFlagsFromList(array $flagIds)
    {
        $flagList = array();
        if (empty($flagIds)) return $flagList;
        
        $db = Registry::get('db');
        $inQuery = implode(',', array_fill(0, count($flagIds), '?'));
        $sql = 'SELECT * FROM ' . DB_PREFIX . 'flags WHERE flag_id IN (' . $inQuery . ')';
        $result = $db->fetchAll($sql, $flagIds);

        foreach($result as $flagRecord) {
            $flagList[] = $this->_map($flagRecord);
        }
        return $flagList;
    }

    public function delete($flagId)
    {
        $db = Registry::get('db');
        $query = 'DELETE FROM ' . DB_PREFIX . 'flags WHERE flag_id = :flagId';
        $db->query($query, array(':flagId' => $flagId));
    }
}