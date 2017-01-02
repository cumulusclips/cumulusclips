<?php

class PlaylistMapper extends MapperAbstract
{
    /**
     * var string Type identifier for watch later playlists
     */
    const TYPE_WATCH_LATER = 'watch_later';

    /**
     * var string Type identifier for favorites playlists
     */
    const TYPE_FAVORITES = 'favorites';

    /**
     * var string Type identifier for custom playlists
     */
    const TYPE_PLAYLIST = 'playlist';

    public function getPlaylistById($playlistId)
    {
        return $this->getPlaylistByCustom(array('playlist_id' => $playlistId));
    }

    public function getUserPlaylists($userId)
    {
        return $this->getMultiplePlaylistsByCustom(array('user_id' => $userId));
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
        $query = preg_replace('/\sAND\s$/', '', $query);

        $dbResults = $db->fetchRow($query, $queryParams);
        if ($db->rowCount() > 0) {
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
        $query = preg_replace('/\sAND\s$/', '', $query);
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
        $db = Registry::get('db');
        if (!empty($playlist->playlistId)) {
            // Update
            $query = 'UPDATE ' . DB_PREFIX . 'playlists SET';
            $query .= ' name = :name, user_id = :userId, type = :type, public = :public, date_created = :dateCreated';
            $query .= ' WHERE playlist_id = :playlistId';
            $bindParams = array(
                ':playlistId' => $playlist->playlistId,
                ':name' => (!empty($playlist->name)) ? $playlist->name : null,
                ':userId' => $playlist->userId,
                ':type' => $playlist->type,
                ':public' => (isset($playlist->public) && $playlist->public === true) ? 1 : 0,
                ':dateCreated' => date(DATE_FORMAT, strtotime($playlist->dateCreated))
            );
        } else {
            // Create
            $query = 'INSERT INTO ' . DB_PREFIX . 'playlists';
            $query .= ' (name, user_id, type, public, date_created)';
            $query .= ' VALUES (:name, :userId, :type, :public, :dateCreated)';
            $bindParams = array(
                ':name' => (!empty($playlist->name)) ? $playlist->name : null,
                ':userId' => $playlist->userId,
                ':type' => (!empty($playlist->type)) ? $playlist->type : 'playlist',
                ':public' => (isset($playlist->public) && $playlist->public === true) ? 1 : 0,
                ':dateCreated' => gmdate(DATE_FORMAT)
            );
        }

        $db->query($query, $bindParams);
        $playlistId = (!empty($playlist->playlistId)) ? $playlist->playlistId : $db->lastInsertId();
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

    public function delete($playlistId)
    {
        $db = Registry::get('db');
        $query = 'DELETE FROM ' . DB_PREFIX . 'playlists WHERE playlist_id = :playlistId';
        $db->query($query, array(':playlistId' => $playlistId));
    }

    protected function _getPlaylistEntryMapper()
    {
        return new PlaylistEntryMapper();
    }
}