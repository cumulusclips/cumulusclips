<?php

class RatingMapper extends MapperAbstract
{
    public function getRatingById($ratingId)
    {
        return $this->getRatingByCustom(array('rating_id' => $ratingId));
    }
    
    public function getRatingByCustom(array $params)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'ratings WHERE ';
        
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
    
    public function getMultipleRatingsByCustom(array $params)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'ratings WHERE ';
        
        $queryParams = array();
        foreach ($params as $fieldName => $value) {
            $query .= "$fieldName = :$fieldName AND ";
            $queryParams[":$fieldName"] = $value;
        }
        $query = preg_replace('/\sAND\s$/', '', $query);
        $dbResults = $db->fetchAll($query, $queryParams);
        
        $ratingsList = array();
        foreach($dbResults as $record) {
            $ratingsList[] = $this->_map($record);
        }
        return $ratingsList;
    }

    protected function _map($dbResults)
    {
        $rating = new Rating();
        $rating->ratingId = $dbResults['rating_id'];
        $rating->videoId = $dbResults['video_id'];
        $rating->userId = $dbResults['user_id'];
        $rating->rating = ($dbResults['rating'] == '1') ? 1 : 0;
        $rating->dateCreated = date(DATE_FORMAT, strtotime($dbResults['date_created']));
        return $rating;
    }

    public function save(Rating $rating)
    {
        $db = Registry::get('db');
        if (!empty($rating->ratingId)) {
            // Update
            $query = 'UPDATE ' . DB_PREFIX . 'ratings SET';
            $query .= ' video_id = :videoId, user_id = :userId, rating = :rating, date_created = :dateCreated';
            $query .= ' WHERE ratingId = :ratingId';
            $bindParams = array(
                ':ratingId' => $rating->ratingId,
                ':videoId' => $rating->videoId,
                ':userId' => $rating->userId,
                ':rating' => (isset($rating->rating) && $rating->rating == 1) ? 1 : 0,
                ':dateCreated' => date(DATE_FORMAT, strtotime($rating->dateCreated))
            );
        } else {
            // Create
            $query = 'INSERT INTO ' . DB_PREFIX . 'ratings ';
            $query .= '(video_id, user_id, rating, date_created) ';
            $query .= 'VALUES (:videoId, :userId, :rating, :dateCreated)';
            $bindParams = array(
                ':videoId' => $rating->videoId,
                ':userId' => $rating->userId,
                ':rating' => (isset($rating->rating) && $rating->rating == 1) ? 1 : 0,
                ':dateCreated' => gmdate(DATE_FORMAT)
            );
        }
            
        $db->query($query, $bindParams);
        $ratingId = (!empty($rating->userId)) ? $rating->ratingId : $db->lastInsertId();
        return $ratingId;
    }
    
    /**
     * Retrieve total number of like ratings for a video
     * @param integer $videoId Video to retrieve likes for
     * @return integer Returns total likes
     */
    public function getLikeCount($videoId)
    {
        $db = Registry::get('db');
        $query = "SELECT COUNT(rating_id) as count FROM " . DB_PREFIX . "ratings WHERE video_id = $videoId AND rating = 1";
        $count = $db->fetchRow($query);
        return $count['count'];
    }

    /**
     * Retrieve total number of dislike ratings for a video
     * @param integer $videoId Video to retrieve dislikes for
     * @return integer Returns total dislikes
     */
    public function getDislikeCount($videoId)
    {
        $db = Registry::get('db');
        $query = "SELECT COUNT(rating_id) as count FROM " . DB_PREFIX . "ratings WHERE video_id = $videoId AND rating = 0";
        $count = $db->fetchRow($query);
        return $count['count'];
    }

    /**
     * Retrieve total number of ratings for a video
     * @param integer $videoId Video to retrieve rating count for
     * @return integer Returns total number of ratings
     */
    public function getRatingCount($videoId)
    {
        $db = Registry::get('db');
        $query = "SELECT COUNT(rating_id) as count FROM " . DB_PREFIX . "ratings WHERE video_id = $videoId";
        $count = $db->fetchRow($query);
        return $count['count'];
    }
    
    public function delete($ratingId)
    {
        $db = Registry::get('db');
        $query = 'DELETE FROM ' . DB_PREFIX . 'ratings WHERE rating_id = :ratingId';
        $db->query($query, array(':ratingId' => $ratingId));
    }
}