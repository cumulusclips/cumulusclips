<?php

class CommentMapper extends MapperAbstract
{
    public function getCommentById($commentId)
    {
        return $this->getCommentByCustom(array('comment_id' => $commentId));
    }
    
    public function getVideoComments($videoId)
    {
        return $this->getMultipleCommentByCustom(array('video_id' => $videoId));
    }

    public function getCommentByCustom(array $params)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'comments WHERE ';
        
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
    
    public function getMultipleCommentsByCustom(array $params)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'comments  WHERE ';
        
        $queryParams = array();
        foreach ($params as $fieldName => $value) {
            $query .= "$fieldName = :$fieldName AND ";
            $queryParams[":$fieldName"] = $value;
        }
        $query = preg_replace('/\sAND\s$/', '', $query);
        $dbResults = $db->fetchAll($query, $queryParams);
        
        $commentsList = array();
        foreach($dbResults as $record) {
            $commentsList[] = $this->_map($record);
        }
        return $commentsList;
    }

    protected function _map($dbResults)
    {
        $comment = new Comment();
        $comment->commentId = $dbResults['comment_id'];
        $comment->userId = $dbResults['user_id'];
        $comment->videoId = $dbResults['video_id'];
        $comment->parentId = $dbResults['parent_id'];
        $comment->comments = $dbResults['comments'];
        $comment->dateCreated = date(DATE_FORMAT, strtotime($dbResults['date_created']));
        $comment->status = $dbResults['status'];
        $comment->email = $dbResults['email'];
        $comment->name = $dbResults['name'];
        $comment->ip = $dbResults['ip'];
        $comment->userAgent = $dbResults['user_agent'];
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
            $query .= ' user_id = :userId, video_id = :videoId, parent_id = :parentId, comments = :comments, date_created = :dateCreated, status = :status, email = :email, name = :name, ip = :ip, user_agent = :userAgent, released = :released';
            $query .= ' WHERE comment_id = :commentId';
            $bindParams = array(
                ':commentId' => $comment->commentId,
                ':userId' => (!empty($comment->userId)) ? $comment->userId : 0,
                ':videoId' => $comment->videoId,
                ':parentId' => (!empty($comment->parentId)) ? $comment->parentId : 0,
                ':comments' => $comment->comments,
                ':dateCreated' => date(DATE_FORMAT, strtotime($comment->dateCreated)),
                ':status' => $comment->status,
                ':email' => (!empty($comment->email)) ? $comment->email : null,
                ':name' => (!empty($comment->name)) ? $comment->name : null,
                ':ip' => (!empty($comment->ip)) ? $comment->ip : null,
                ':userAgent' => (!empty($comment->userAgent)) ? $comment->userAgent : null,
                ':released' => (isset($comment->released) && $comment->released === true) ? 1 : 0,
            );
        } else {
            // Create
            Plugin::triggerEvent('video.create', $comment);
            $query = 'INSERT INTO ' . DB_PREFIX . 'comments';
            $query .= ' (user_id, video_id, parent_id, comments, date_created, status, email, name, ip, user_agent, released)';
            $query .= ' VALUES (:userId, :videoId, :parentId, :comments, :dateCreated, :status, :email, :name, :ip, :userAgent, :released)';
            $bindParams = array(
                ':userId' => (!empty($comment->userId)) ? $comment->userId : 0,
                ':videoId' => $comment->videoId,
                ':parentId' => (!empty($comment->parentId)) ? $comment->parentId : 0,
                ':comments' => $comment->comments,
                ':dateCreated' => gmdate(DATE_FORMAT),
                ':status' => (!empty($comment->status)) ? $comment->status : 'new',
                ':email' => (!empty($comment->email)) ? $comment->email : null,
                ':name' => (!empty($comment->name)) ? $comment->name : null,
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
    
    public function getCommentsFromList(array $commentIds)
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
    
    public function delete($commentId)
    {
        $db = Registry::get('db');
        $db->query('DELETE FROM ' . DB_PREFIX . 'comments WHERE comment_id = :commentId', array(':commentId' => $commentId));
    }

    public function getCommentIds($videoId, $limit, $parentCommentId = 0, $offsetCommentId = 0)
    {
        $db = Registry::get('db');
        $sql = 'SELECT comment_id FROM ' . DB_PREFIX . 'comments ';
        $where = 'video_id = :videoId AND parent_id = :parentId';
        
        $params = array(
            ':videoId' => $videoId,
            ':parentId' => $parentCommentId
        );
        
        if (!empty($offsetCommentId)) {
            $params[':offsetId'] = $offsetCommentId;
            $where .= ' AND comment_id > :offsetId';
        }
        
        $sql .= 'WHERE ' . $where . ' LIMIT ' . (int) $limit;
        $result = $db->fetchAll($sql, $params);
        return Functions::arrayColumn($result, 'comment_id');
    }
    
    public function getVideoCommentCount($videoId)
    {
        $db = Registry::get('db');
        $sql = 'SELECT COUNT(comment_id) AS count FROM ' . DB_PREFIX . 'comments WHERE video_id = ? AND status = "approved"';
        $result = $db->fetchRow($sql, array($videoId));
        return $result['count'];
    }
}