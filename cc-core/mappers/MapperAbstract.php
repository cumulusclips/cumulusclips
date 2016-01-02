<?php

abstract class MapperAbstract
{
    /**
     * Retrieve a record matching the given parameters
     * @params array $params Key/value list of params to filter records by
     * @return Model Returns first matching record
     */
    public function getByCustom(array $params)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . static::TABLE . ' WHERE ';
        
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

    /**
     * Retrieve a record by it's id
     * @param int $id Id of the record being retrieved
     * @return Model Returns the matching record
     */
    public function getById($id)
    {
        return $this->getByCustom(array(static::KEY => $id));
    }

    /**
     * Retrieve a list of records matching the given parameters
     * @params array $params Key/value list of params to filter records by
     * @return Model[] Returns all the matching records
     */
    public function getMultipleByCustom(array $params)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . static::TABLE . ' WHERE ';
        
        $queryParams = array();
        foreach ($params as $fieldName => $value) {
            $query .= "$fieldName = :$fieldName AND ";
            $queryParams[":$fieldName"] = $value;
        }
        $query = preg_replace('/\sAND\s$/', '', $query);
        $dbResults = $db->fetchAll($query, $queryParams);
        
        $recordList = array();
        foreach($dbResults as $record) {
            $recordList[] = $this->_map($record);
        }
        return $recordList;
    }

    /**
     * Retrieves a list of records identified by given ids
     * @param array $ids Ids of records to retrieve
     * @return Model[] Returns list of records
     */
    public function getFromList(array $ids)
    {
        $recordList = array();
        if (empty($ids)) return $recordList;
        
        $db = Registry::get('db');
        $inQuery = implode(',', array_fill(0, count($ids), '?'));
        $sql = 'SELECT * FROM ' . DB_PREFIX . static::TABLE . ' WHERE ' . static::KEY . ' IN (' . $inQuery . ')';
        $result = $db->fetchAll($sql, $ids);

        foreach($result as $record) {
            $recordList[] = $this->_map($record);
        }
        return $recordList;
    }

    /**
     * Delete a record
     * @param int $id Id of record to be deleted
     * @return MapperAbstract Provides fluent interface
     */
    public function delete($id)
    {
        $db = Registry::get('db');
        $query = 'DELETE FROM ' . DB_PREFIX . static::TABLE . ' WHERE ' . static::KEY . ' = :idValue';
        $db->query($query, array(':idValue' => $id));
        return $this;
    }

    /**
     * Maps the values from a data source record to the properties in a data model
     * @param array $record The record from the data source containing the data
     * @return Model Returns an instance of a data model populated with the record's data
     */
    abstract protected function _map($record);
}