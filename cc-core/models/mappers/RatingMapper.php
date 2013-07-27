<?php

class RatingMapper
{
    public function getRatingById($ratingId)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'ratings WHERE ratingId = :ratingId';
        $dbResults = $db->fetchRow($query, array(':ratingId' => $ratingId));
        if ($db->rowCount() == 1) {
            return $this->_map($dbResults);
        } else {
            return false;
        }
    }

    protected function _map($dbResults)
    {
        $rating = new Rating();
        $rating->ratingId = $dbResults['ratingId'];
        $rating->videoId = $dbResults['videoId'];
        $rating->userId = $dbResults['userId'];
        $rating->rating = $dbResults['rating'];
        $rating->dateCreated = date(DATE_FORMAT, strtotime($dbResults['dateCreated']));
        return $rating;
    }

    public function save(Rating $rating)
    {
        $rating = Plugin::triggerFilter('video.beforeSave', $rating);
        $db = Registry::get('db');
        if (!empty($rating->ratingId)) {
            // Update
            Plugin::triggerEvent('video.update', $rating);
            $query = 'UPDATE ' . DB_PREFIX . 'ratings SET';
            $query .= ' videoId = :videoId, userId = :userId, rating = :rating, dateCreated = :dateCreated';
            $query .= ' WHERE ratingId = :ratingId';
            $bindParams = array(
                ':ratingId' => $rating->ratingId,
                ':videoId' => $rating->videoId,
                ':userId' => $rating->userId,
                ':rating' => $rating->rating,
                ':dateCreated' => date(DATE_FORMAT, strtotime($rating->dateCreated))
            );
        } else {
            // Create
            Plugin::triggerEvent('video.create', $rating);
            $query = 'INSERT INTO ' . DB_PREFIX . 'ratings';
            $query .= ' (videoId, userId, rating, dateCreated)';
            $query .= ' VALUES (:videoId, :userId, :rating, :dateCreated)';
            $bindParams = array(
                ':videoId' => $rating->videoId,
                ':userId' => $rating->userId,
                ':rating' => $rating->rating,
                ':dateCreated' => gmdate(DATE_FORMAT)
            );
        }
            
        $db->query($query, $bindParams);
        $ratingId = (!empty($rating->userId)) ? $rating->ratingId : $db->lastInsertId();
        Plugin::triggerEvent('video.save', $ratingId);
        return $ratingId;
    }
}