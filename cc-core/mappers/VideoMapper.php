<?php

class VideoMapper extends MapperAbstract
{
    /**
     * @var string Status of videos newly created in DB but not yet uploaded
     */
    const NEW_VIDEO = 'new';

    /**
     * @var string Status of videos approved and available for viewing
     */
    const APPROVED = 'approved';

    /**
     * @var string Status of videos banned by administrators
     */
    const BANNED = 'banned';

    /**
     * @var string Status of videos currently being transcoded
     */
    const PROCESSING = 'processing';

    /**
     * @var string Status of videos when they fail to complete transcoding
     */
    const FAILED = 'failed';

    /**
     * @var string Status of videos that have been uploaded and are ready for transcoding
     */
    const PENDING_CONVERSION = 'pending_conversion';

    /**
     * @var string Status of videos that have been transcoded but require admin approval to be available for viewing
     */
    const PENDING_APPROVAL = 'pending_approval';

    public function getVideoById($videoId)
    {
        return $this->getVideoByCustom(array('video_id' => $videoId));
    }

    public function getVideoByFilename($filename)
    {
        return $this->getVideoByCustom(array('filename' => $filename));
    }

    public function getUserVideos($userId)
    {
        return $this->getMultipleVideosByCustom(array('user_id' => $userId));
    }

    public function getVideoByCustom(array $params)
    {
        $db = Registry::get('db');
        $query = 'SELECT ' . DB_PREFIX . 'videos.*, username FROM ' . DB_PREFIX . 'videos INNER JOIN ' . DB_PREFIX . 'users ON ' . DB_PREFIX . 'videos.user_id = ' . DB_PREFIX . 'users.user_id WHERE ';

        $queryParams = array();
        foreach ($params as $fieldName => $value) {
            $query .= DB_PREFIX . "videos.$fieldName = :$fieldName AND ";
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

    public function getMultipleVideosByCustom(array $params)
    {
        $db = Registry::get('db');
        $query = 'SELECT ' . DB_PREFIX . 'videos.*, username FROM ' . DB_PREFIX . 'videos INNER JOIN ' . DB_PREFIX . 'users ON ' . DB_PREFIX . 'videos.user_id = ' . DB_PREFIX . 'users.user_id WHERE ';

        $queryParams = array();
        foreach ($params as $fieldName => $value) {
            $query .= DB_PREFIX . "videos.$fieldName = :$fieldName AND ";
            $queryParams[":$fieldName"] = $value;
        }
        $query = preg_replace('/\sAND\s$/', '', $query);
        $dbResults = $db->fetchAll($query, $queryParams);

        $videosList = array();
        foreach($dbResults as $record) {
            $videosList[] = $this->_map($record);
        }
        return $videosList;
    }

    protected function _map($dbResults)
    {
        $video = new Video();
        $video->videoId = $dbResults['video_id'];
        $video->filename = $dbResults['filename'];
        $video->title = $dbResults['title'];
        $video->description = $dbResults['description'];
        $video->tags = explode(', ', $dbResults['tags']);
        $video->categoryId = $dbResults['category_id'];
        $video->userId = $dbResults['user_id'];
        $video->username = $dbResults['username'];
        $video->dateCreated = date(DATE_FORMAT, strtotime($dbResults['date_created']));
        $video->jobId = $dbResults['job_id'];
        $video->duration = Functions::formatDuration($dbResults['duration']);
        $video->status = $dbResults['status'];
        $video->views = $dbResults['views'];
        $video->originalExtension = $dbResults['original_extension'];
        $video->featured = ($dbResults['featured'] == 1) ? true : false;
        $video->gated = ($dbResults['gated'] == 1) ? true : false;
        $video->released = ($dbResults['released'] == 1) ? true : false;
        $video->disableEmbed = ($dbResults['disable_embed'] == 1) ? true : false;
        $video->private = ($dbResults['private'] == 1) ? true : false;
        $video->privateUrl = $dbResults['private_url'];
        $video->commentsClosed = ($dbResults['comments_closed'] == 1) ? true : false;
        return $video;
    }

    public function save(Video $video)
    {
        $db = Registry::get('db');
        if (!empty($video->videoId)) {
            // Update
            $query = 'UPDATE ' . DB_PREFIX . 'videos SET';
            $query .= ' filename = :filename, title = :title, description = :description, tags = :tags, category_id = :categoryId, user_id = :userId, date_created = :dateCreated, job_id = :jobId, duration = :duration, status = :status, views = :views, original_extension = :originalExtension, featured = :featured, gated = :gated, released = :released, disable_embed = :disableEmbed, private = :private, private_url = :privateUrl, comments_closed = :commentsClosed';
            $query .= ' WHERE video_id = :videoId';
            $bindParams = array(
                ':videoId' => $video->videoId,
                ':filename' => $video->filename,
                ':title' => $video->title,
                ':description' => $video->description,
                ':tags' => implode(', ', $video->tags),
                ':categoryId' => $video->categoryId,
                ':userId' => $video->userId,
                ':dateCreated' => date(DATE_FORMAT, strtotime($video->dateCreated)),
                ':jobId' => (!empty($video->jobId)) ? $video->jobId : null,
                ':duration' => (!empty($video->duration)) ? $video->duration : null,
                ':status' => (!empty($video->status)) ? $video->status : static::NEW_VIDEO,
                ':views' => (!empty($video->views)) ? $video->views : 0,
                ':originalExtension' => (!empty($video->originalExtension)) ? $video->originalExtension : null,
                ':featured' => (isset($video->featured) && $video->featured === true) ? 1 : 0,
                ':gated' => (isset($video->gated) && $video->gated === true) ? 1 : 0,
                ':released' => (isset($video->released) && $video->released === true) ? 1 : 0,
                ':disableEmbed' => (isset($video->disableEmbed) && $video->disableEmbed === true) ? 1 : 0,
                ':private' => (isset($video->private) && $video->private === true) ? 1 : 0,
                ':privateUrl' => (!empty($video->privateUrl)) ? $video->privateUrl : null,
                ':commentsClosed' => (isset($video->commentsClosed) && $video->commentsClosed === true) ? 1 : 0
            );
        } else {
            // Create
            $query = 'INSERT INTO ' . DB_PREFIX . 'videos';
            $query .= ' (filename, title, description, tags, category_id, user_id, date_created, job_id, duration, status, views, original_extension, featured, gated, released, disable_embed, private, private_url, comments_closed)';
            $query .= ' VALUES (:filename, :title, :description, :tags, :categoryId, :userId, :dateCreated, :jobId, :duration, :status, :views, :originalExtension, :featured, :gated, :released, :disableEmbed, :private, :privateUrl, :commentsClosed)';
            $bindParams = array(
                ':filename' => $video->filename,
                ':title' => $video->title,
                ':description' => $video->description,
                ':tags' => implode(', ', $video->tags),
                ':categoryId' => $video->categoryId,
                ':userId' => $video->userId,
                ':dateCreated' => gmdate(DATE_FORMAT),
                ':jobId' => (!empty($video->jobId)) ? $video->jobId : null,
                ':duration' => (!empty($video->duration)) ? $video->duration : null,
                ':status' => (!empty($video->status)) ? $video->status : static::NEW_VIDEO,
                ':views' => (!empty($video->views)) ? $video->views : 0,
                ':originalExtension' => (!empty($video->originalExtension)) ? $video->originalExtension : null,
                ':featured' => (isset($video->featured) && $video->featured === true) ? 1 : 0,
                ':gated' => (isset($video->gated) && $video->gated === true) ? 1 : 0,
                ':released' => (isset($video->released) && $video->released === true) ? 1 : 0,
                ':disableEmbed' => (isset($video->disableEmbed) && $video->disableEmbed === true) ? 1 : 0,
                ':private' => (isset($video->private) && $video->private === true) ? 1 : 0,
                ':privateUrl' => (!empty($video->privateUrl)) ? $video->privateUrl : null,
                ':commentsClosed' => (isset($video->commentsClosed) && $video->commentsClosed === true) ? 1 : 0
            );
        }

        $db->query($query, $bindParams);
        $videoId = (!empty($video->videoId)) ? $video->videoId : $db->lastInsertId();
        return $videoId;
    }

    public function getVideosFromList(array $videoIds)
    {
        $videoList = array();
        if (empty($videoIds)) return $videoList;

        $db = Registry::get('db');
        $inQuery = implode(',', array_fill(0, count($videoIds), '?'));
        $sql = 'SELECT ' . DB_PREFIX . 'videos.*, username FROM ' . DB_PREFIX . 'videos INNER JOIN ' . DB_PREFIX . 'users ON ' . DB_PREFIX . 'videos.user_id = ' . DB_PREFIX . 'users.user_id ';
        $sql .= 'WHERE video_id IN (' . $inQuery . ') ';
        $sql .= 'ORDER BY FIELD(video_id, ' . $inQuery . ')';
        $result = $db->fetchAll($sql, array_merge($videoIds, $videoIds));

        foreach($result as $videoRecord) {
            $videoList[] = $this->_map($videoRecord);
        }
        return $videoList;
    }

    /**
     * Deletes given video from data storage
     *
     * @param int $videoId ID of video being deleted
     */
    public function delete($videoId)
    {
        $db = Registry::get('db');
        $query = 'DELETE FROM ' . DB_PREFIX . 'videos WHERE video_id = :videoId';
        $db->query($query, array(':videoId' => $videoId));
    }

    /**
     * Get video count Method
     *
     * @param int $userId Id of user to retrieve video count for
     * @return integer Returns the number of approved videos uploaded by the user
     */
    public function getVideoCount($userId)
    {
        $db = Registry::get('db');
        $query = "SELECT COUNT(video_id) AS count FROM " . DB_PREFIX . "videos WHERE user_id = $userId AND status = 'approved' AND private = 0";
        $result = $db->fetchRow($query);
        return $result['count'];
    }
}