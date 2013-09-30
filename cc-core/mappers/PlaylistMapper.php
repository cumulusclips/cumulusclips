<?php

class PlaylistMapper extends MapperAbstract
{
    public function getPlaylistById($playlistId)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'playlists WHERE playlistId = :playlistId';
        $dbResults = $db->fetchRow($query, array(':playlistId' => $playlistId));
        if ($db->rowCount() == 1) {
            return $this->_map($dbResults);
        } else {
            return false;
        }
    }

    protected function _map($dbResults)
    {
        $playlist = new Playlist();
        $playlist->playlistId = $dbResults['playlistId'];
        $playlist->name = $dbResults['name'];
        $playlist->userId = $dbResults['userId'];
        $playlist->type = $dbResults['type'];
        $playlist->public = ($dbResults['public'] == 1) ? true : false;
        $playlist->dateCreated = date(DATE_FORMAT, strtotime($dbResults['dateCreated']));
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
            $query .= ' name = :name, userId = :userId, type = :type, public = :public, dateCreated = :dateCreated';
            $query .= ' WHERE playlistId = :playlistId';
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
            $query .= ' (name, userId, type, public, dateCreated)';
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
}