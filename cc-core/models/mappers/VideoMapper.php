<?php

class VideoMapper
{
    public function getVideoById($videoId)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'videos WHERE videoId = :videoId';
        $dbResults = $db->fetchRow($query, array(':videoId' => $videoId));
        if ($db->rowCount() == 1) {
            return $this->_map($dbResults);
        } else {
            return false;
        }
    }
    
    public function getVideoByFilename($filename)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'videos WHERE filename = :filename';
        $dbResults = $db->fetchRow($query, array(':filename' => $filename));
        if ($db->rowCount() == 1) {
            return $this->_map($dbResults);
        } else {
            return false;
        }
    }
    
    public function getUserVideos($userId)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'videos WHERE userId = :userId';
        $dbResults = $db->fetchAll($query, array(':userId' => $userId));
        $userVideos = array();
        foreach($dbResults as $record) {
            $userVideos[] = $this->_map($record);
        }
        return $userVideos;
    }

    protected function _map($dbResults)
    {
        $video = new Video();
        $video->videoId = $dbResults['videoId'];
        $video->filename = $dbResults['filename'];
        $video->title = $dbResults['title'];
        $video->description = $dbResults['description'];
        $video->tags = explode(', ', $dbResults['tags']);
        $video->categoryId = $dbResults['categoryId'];
        $video->userId = $dbResults['userId'];
        $video->dateCreated = date(DATE_FORMAT, strtotime($dbResults['dateCreated']));
        $video->duration = $dbResults['duration'];
        $video->status = ($dbResults['status'] == 1) ? true : false;
        $video->views = $dbResults['views'];
        $video->originalExtension = $dbResults['originalExtension'];
        $video->featured = ($dbResults['featured'] == 1) ? true : false;
        $video->gated = ($dbResults['gated'] == 1) ? true : false;
        $video->released = ($dbResults['released'] == 1) ? true : false;
        $video->disableEmbed = ($dbResults['disableEmbed'] == 1) ? true : false;
        $video->private = ($dbResults['private'] == 1) ? true : false;
        $video->privateUrl = $dbResults['privateUrl'];
        return $video;
    }

    public function save(Video $video)
    {
        $video = Plugin::triggerFilter('video.beforeSave', $video);
        $db = Registry::get('db');
        if (!empty($video->videoId)) {
            // Update
            Plugin::triggerEvent('video.update', $video);
            $query = 'UPDATE ' . DB_PREFIX . 'videos SET';
            $query .= ' filename = :filename, title = :title, description = :description, tags = :tags, categoryId = :categoryId, userId = :userId, dateCreated = :dateCreated, duration = :duration, status = :status, views = :views, originalExtension = :originalExtension, featured = :featured, gated = :gated, released = :released, disableEmbed = :disableEmbed, private = :private, privateUrl = :privateUrl';
            $query .= ' WHERE videoId = :videoId';
            $bindParams = array(
                ':videoId' => $video->videoId,
                ':filename' => $video->filename,
                ':title' => $video->title,
                ':description' => $video->description,
                ':tags' => implode(', ', $video->tags),
                ':categoryId' => $video->categoryId,
                ':userId' => $video->userId,
                ':dateCreated' => date(DATE_FORMAT, strtotime($video->dateCreated)),
                ':duration' => (!empty($video->duration)) ? $video->duration : null,
                ':status' => (!empty($video->status)) ? $video->status : 'new',
                ':views' => (!empty($video->views)) ? $video->views : 0,
                ':originalExtension' => (!empty($video->originalExtension)) ? $video->originalExtension : null,
                ':featured' => (isset($video->featured) && $video->featured === true) ? 1 : 0,
                ':gated' => (isset($video->gated) && $video->gated === true) ? 1 : 0,
                ':released' => (isset($video->released) && $video->released === true) ? 1 : 0,
                ':disableEmbed' => (isset($video->disableEmbed) && $video->disableEmbed === true) ? 1 : 0,
                ':private' => (isset($video->private) && $video->private === true) ? 1 : 0,
                ':privateUrl' => (!empty($video->privateUrl)) ? $video->privateUrl : null,
            );
        } else {
            // Create
            Plugin::triggerEvent('video.create', $video);
            $query = 'INSERT INTO ' . DB_PREFIX . 'videos';
            $query .= ' (filename, title, description, tags, categoryId, userId, dateCreated, duration, status, views, originalExtension, featured, gated, released, disableEmbed, private, privateUrl)';
            $query .= ' VALUES (:filename, :title, :description, :tags, :categoryId, :userId, :dateCreated, :duration, :status, :views, :originalExtension, :featured, :gated, :released, :disableEmbed, :private, :privateUrl)';
            $bindParams = array(
                ':filename' => $video->filename,
                ':title' => $video->title,
                ':description' => $video->description,
                ':tags' => implode(', ', $video->tags),
                ':categoryId' => $video->categoryId,
                ':userId' => $video->userId,
                ':dateCreated' => gmdate(DATE_FORMAT),
                ':duration' => (!empty($video->duration)) ? $video->duration : null,
                ':status' => (!empty($video->status)) ? $video->status : 'new',
                ':views' => (!empty($video->views)) ? $video->views : 0,
                ':originalExtension' => (!empty($video->originalExtension)) ? $video->originalExtension : null,
                ':featured' => (isset($video->featured) && $video->featured === true) ? 1 : 0,
                ':gated' => (isset($video->gated) && $video->gated === true) ? 1 : 0,
                ':released' => (isset($video->released) && $video->released === true) ? 1 : 0,
                ':disableEmbed' => (isset($video->disableEmbed) && $video->disableEmbed === true) ? 1 : 0,
                ':private' => (isset($video->private) && $video->private === true) ? 1 : 0,
                ':privateUrl' => (!empty($video->privateUrl)) ? $video->privateUrl : null,
            );
        }

        $db->query($query, $bindParams);
        $videoId = (!empty($video->videoId)) ? $video->videoId : $db->lastInsertId();
        Plugin::triggerEvent('video.save', $videoId);
        return $videoId;
    }
}