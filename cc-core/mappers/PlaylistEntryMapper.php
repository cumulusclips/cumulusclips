<?php

class PlaylistEntryMapper extends MapperAbstract
{
    public function getPlaylistEntriesByPlaylistId($playlistId)
    {
        return $this->getMultiplePlaylistEntriesByCustom(array('playlist_id' => $playlistId));
    }
    
    public function getPlaylistEntryByCustom(array $params)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'playlist_entries WHERE ';
        
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
    
    public function getMultiplePlaylistEntriesByCustom(array $params)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'playlist_entries WHERE ';
        
        $queryParams = array();
        foreach ($params as $fieldName => $value) {
            $query .= "$fieldName = :$fieldName AND ";
            $queryParams[":$fieldName"] = $value;
        }
        $query = preg_replace('/\sAND\s$/', '', $query);
        $dbResults = $db->fetchAll($query, $queryParams);
        
        $playlistEntryList = array();
        foreach($dbResults as $record) {
            $playlistEntryList[] = $this->_map($record);
        }
        return $playlistEntryList;
    }

    protected function _map($dbResults)
    {
        $playlistEntry = new PlaylistEntry();
        $playlistEntry->playlistEntryId = $dbResults['playlist_entry_id'];
        $playlistEntry->playlistId = $dbResults['playlist_id'];
        $playlistEntry->videoId = $dbResults['video_id'];
        $playlistEntry->dateCreated = date(DATE_FORMAT, strtotime($dbResults['date_created']));
        return $playlistEntry;
    }

    public function save(PlaylistEntry $playlistEntry)
    {
        $db = Registry::get('db');
        if (!empty($playlistEntry->playlistEntryId)) {
            // Update
            $query = 'UPDATE ' . DB_PREFIX . 'playlist_entries SET';
            $query .= ' playlist_id = :playlistId, video_id = :videoId, date_created = :dateCreated';
            $query .= ' WHERE playlist_id = :playlistId';
            $bindParams = array(
                ':playlistId' => $playlistEntry->playlistId,
                ':videoId' => $playlistEntry->videoId,
                ':dateCreated' => date(DATE_FORMAT, strtotime($playlist->dateCreated))
            );
        } else {
            // Create
            $query = 'INSERT INTO ' . DB_PREFIX . 'playlist_entries';
            $query .= ' (playlist_id, video_id, date_created)';
            $query .= ' VALUES (:playlistId, :videoId, :dateCreated)';
            $bindParams = array(
                ':playlistId' => $playlistEntry->playlistId,
                ':videoId' => $playlistEntry->videoId,
                ':dateCreated' => gmdate(DATE_FORMAT)
            );
        }
            
        $db->query($query, $bindParams);
        $playlistEntryId = (!empty($playlistEntry->playlistEntryId)) ? $playlistEntry->playlistEntryId : $db->lastInsertId();
        return $playlistEntryId;
    }
    
    public function getPlaylistEntriesFromList(array $playlistEntryIds)
    {
        $playlistEntryList = array();
        if (empty($playlistEntryIds)) return $playlistEntryList;
        
        $db = Registry::get('db');
        $inQuery = implode(',', array_fill(0, count($playlistEntryIds), '?'));
        $sql = 'SELECT * FROM ' . DB_PREFIX . 'playlist_entries WHERE playlist_entry_id IN (' . $inQuery . ')';
        $result = $db->fetchAll($sql, $playlistEntryIds);

        foreach($result as $playlistEntryRecord) {
            $playlistEntryList[] = $this->_map($playlistEntryRecord);
        }
        return $playlistEntryList;
    }
    
    public function delete($playlistEntryId)
    {
        $db = Registry::get('db');
        $query = 'DELETE FROM ' . DB_PREFIX . 'playlist_entries WHERE playlist_entry_id = :playlistEntryId';
        $db->query($query, array(':playlistEntryId' => $playlistEntryId));
    }
}