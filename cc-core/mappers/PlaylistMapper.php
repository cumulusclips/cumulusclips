<?php

class PlaylistMapper extends MapperAbstract
{
    public function getPlaylistById($playlistId)
    {
        return $this->getPlaylistByCustom(array('playlist_id' => $playlistId));
    }
    
    public function getPlaylistByCustom(array $params)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'playlists WHERE ';
        
        $queryParams = array();
        foreach ($params as $fieldName => $value) {
            $query .= "$fieldName = :$fieldName AND ";
            $queryParams[":$fieldName"] = $value;
        }
        $query = rtrim($query, ' AND ');
        
        $dbResults = $db->fetchRow($query, $queryParams);
        if ($db->rowCount() == 1) {
            $playlist = $this->_map($dbResults);
            $playlistEntryMapper = $this->_getPlaylistEntryMapper();
            $playlist->entries = $playlistEntryMapper->getPlaylistEntriesByPlaylistId($dbResults['playlist_id']);
            return $playlist;
        } else {
            return false;
        }
    }
    
    public function getMultiplePlaylistsByCustom(array $params)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'playlists WHERE ';
        
        $queryParams = array();
        foreach ($params as $fieldName => $value) {
            $query .= "$fieldName = :$fieldName AND ";
            $queryParams[":$fieldName"] = $value;
        }
        $query = rtrim($query, ' AND ');
        $dbResults = $db->fetchAll($query, $queryParams);
        
        $playlistList = array();
        $playlistEntryMapper = $this->_getPlaylistEntryMapper();
        foreach($dbResults as $record) {
            $playlist = $this->_map($record);
            $playlist->entries = $playlistEntryMapper->getPlaylistEntriesByPlaylistId($record['playlist_id']);
            $playlistList[] = $playlist;
        }
        return $playlistList;
    }

    protected function _map($dbResults)
    {
        $playlist = new Playlist();
        $playlist->playlistId = $dbResults['playlist_id'];
        $playlist->name = $dbResults['name'];
        $playlist->userId = $dbResults['user_id'];
        $playlist->type = $dbResults['type'];
        $playlist->public = ($dbResults['public'] == 1) ? true : false;
        $playlist->dateCreated = date(DATE_FORMAT, strtotime($dbResults['date_created']));
        return $playlist;
    }

    public function save(Playlist $playlist)
    {
        $playlist = Plugin::triggerFilter('video.beforeSave', $playlist);
        $db = Registry::get('db');
        if (!empty($playlist->playlistId)) {
            // Update
            Plugin::triggerEvent('video.update', $playlist);
            $query = 'UPDATE ' . DB_PREFIX . 'playlists SET';
            $query .= ' name = :name, user_id = :userId, type = :type, public = :public, date_created = :dateCreated';
            $query .= ' WHERE playlist_id = :playlistId';
            $bindParams = array(
                ':playlistId' => $playlist->playlistId,
                ':name' => $playlist->name,
                ':userId' => $playlist->userId,
                ':type' => $playlist->type,
                ':public' => (isset($playlist->public) && $playlist->public === true) ? 1 : 0,
                ':dateCreated' => date(DATE_FORMAT, strtotime($playlist->dateCreated))
            );
        } else {
            // Create
            Plugin::triggerEvent('video.create', $playlist);
            $query = 'INSERT INTO ' . DB_PREFIX . 'playlists';
            $query .= ' (name, user_id, type, public, date_created)';
            $query .= ' VALUES (:name, :userId, :type, :public, :dateCreated)';
            $bindParams = array(
                ':name' => $playlist->name,
                ':userId' => $playlist->userId,
                ':type' => (!empty($playlist->type)) ? $playlist->type : 'list',
                ':public' => (isset($playlist->public) && $playlist->public === true) ? 1 : 0,
                ':dateCreated' => gmdate(DATE_FORMAT)
            );
        }
            
        $db->query($query, $bindParams);
        $playlistId = (!empty($playlist->playlistId)) ? $playlist->playlistId : $db->lastInsertId();
        Plugin::triggerEvent('video.save', $playlistId);
        return $playlistId;
    }
    
    public function getPlaylistsFromList(array $playlistIds)
    {
        $playlistList = array();
        if (empty($playlistIds)) return $playlistList;
        
        $db = Registry::get('db');
        $inQuery = implode(',', array_fill(0, count($playlistIds), '?'));
        $sql = 'SELECT * FROM ' . DB_PREFIX . 'playlists WHERE playlist_id IN (' . $inQuery . ')';
        $result = $db->fetchAll($sql, $playlistIds);
        
        $playlistEntryMapper = $this->_getPlaylistEntryMapper();
        foreach($result as $playlistRecord) {
            $playlist = $this->_map($playlistRecord);
            $playlist->entries = $playlistEntryMapper->getPlaylistEntriesByPlaylistId($playlistRecord['playlist_id']);
            $playlistList[] = $playlist;
        }
        return $playlistList;
    }
    
    protected function _getPlaylistEntryMapper()
    {
        return new PlaylistEntryMapper();
    }
}