<?php

class CommentMapper extends MapperAbstract
{
    public function getCommentById($commentId)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'comments WHERE commentId = :commentId';
        $dbResults = $db->fetchRow($query, array(':commentId' => $commentId));
        if ($db->rowCount() == 1) {
            return $this->_map($dbResults);
        } else {
            return false;
        }
    }
    
    public function getUserComments($user)
    {
        if (is_numeric($user)) {
            $userField = 'userId';
        } else {
            $userField = 'email';
        }
        
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'comments WHERE ' . $userField . ' = :userValue';
        $dbResults = $db->fetchAll($query, array(':userValue' => $user));
        $commentList = array();
        foreach ($dbResults as $row) {
            $commentList[] = $this->_map($row);
        }
        return $commentList;
    }

    protected function _map($dbResults)
    {
        $comment = new Comment();
        $comment->commentId = $dbResults['commentId'];
        $comment->userId = $dbResults['userId'];
        $comment->videoId = $dbResults['videoId'];
        $comment->comments = $dbResults['comments'];
        $comment->dateCreated = date(DATE_FORMAT, strtotime($dbResults['dateCreated']));
        $comment->status = $dbResults['status'];
        $comment->email = $dbResults['email'];
        $comment->name = $dbResults['name'];
        $comment->website = $dbResults['website'];
        $comment->ip = $dbResults['ip'];
        $comment->userAgent = $dbResults['userAgent'];
        $comment->released = ($dbResults['released'] == 1) ? true : false;
        return $comment;
    }

    public function save(Comment $comment)
    {
        $comment = Plugin::triggerFilter('video.beforeSave', $comment);
        $db = Registry::get('db');
        if (!empty($comment->commentId)) {
            // Update
            Plugin::triggerEvent('video.update', $comment);
            $query = 'UPDATE ' . DB_PREFIX . 'comments SET';
            $query .= ' userId = :userId, videoId = :videoId, comments = :comments, dateCreated = :dateCreated, status = :status, email = :email, name = :name, website = :website, ip = :ip, userAgent = :userAgent, released = :released';
            $query .= ' WHERE commentId = :commentId';
            $bindParams = array(
                ':commentId' => $comment->commentId,
                ':userId' => (!empty($comment->userId)) ? $comment->userId : null,
                ':videoId' => $comment->videoId,
                ':comments' => $comment->comments,
                ':dateCreated' => date(DATE_FORMAT, strtotime($comment->dateCreated)),
                ':status' => $comment->status,
                ':email' => (!empty($comment->email)) ? $comment->email : null,
                ':name' => (!empty($comment->name)) ? $comment->name : null,
                ':website' => (!empty($comment->website)) ? $comment->website : null,
                ':ip' => (!empty($comment->ip)) ? $comment->ip : null,
                ':userAgent' => (!empty($comment->userAgent)) ? $comment->userAgent : null,
                ':released' => (isset($comment->released) && $comment->released === true) ? 1 : 0,
            );
        } else {
            // Create
            Plugin::triggerEvent('video.create', $comment);
            $query = 'INSERT INTO ' . DB_PREFIX . 'comments';
            $query .= ' (userId, videoId, comments, dateCreated, status, email, name, website, ip, userAgent, released)';
            $query .= ' VALUES (:userId, :videoId, :comments, :dateCreated, :status, :email, :name, :website, :ip, :userAgent, :released)';
            $bindParams = array(
                ':userId' => (!empty($comment->userId)) ? $comment->userId : null,
                ':videoId' => $comment->videoId,
                ':comments' => $comment->comments,
                ':dateCreated' => gmdate(DATE_FORMAT),
                ':status' => (!empty($comment->status)) ? $comment->status : 'new',
                ':email' => (!empty($comment->email)) ? $comment->email : null,
                ':name' => (!empty($comment->name)) ? $comment->name : null,
                ':website' => (!empty($comment->website)) ? $comment->website : null,
                ':ip' => (!empty($comment->ip)) ? $comment->ip : null,
                ':userAgent' => (!empty($comment->userAgent)) ? $comment->userAgent : null,
                ':released' => (isset($comment->released) && $comment->released === true) ? 1 : 0,
            );
        }
            
        $db->query($query, $bindParams);
        $commentId = (!empty($comment->commentId)) ? $comment->commentId : $db->lastInsertId();
        Plugin::triggerEvent('video.save', $commentId);
        return $commentId;
    }
    
    public function getMultipleCommentsById(array $commentIds)
    {
        $commentList = array();
        if (empty($commentIds)) return $commentList;
        
        $db = Registry::get('db');
        $inQuery = implode(',', array_fill(0, count($commentIds), '?'));
        $sql = 'SELECT * FROM ' . DB_PREFIX . 'comments WHERE comment_id IN (' . $inQuery . ')';
        $result = $db->fetchAll($sql, $commentIds);

        foreach($result as $commentRecord) {
            $commentList[] = $this->_map($commentRecord);
        }
        return $commentList;
    }
}