<?php

class CommentMapper extends MapperAbstract
{
    public function getCommentById($commentId)
    {
        return $this->getCommentByCustom(array('comment_id' => $commentId));
    }

    public function getVideoCommentsById($videoId)
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
        $comment->commentId = (int) $dbResults['comment_id'];
        $comment->userId = (int) $dbResults['user_id'];
        $comment->videoId = (int) $dbResults['video_id'];
        $comment->parentId = (int) $dbResults['parent_id'];
        $comment->comments = $dbResults['comments'];
        $comment->dateCreated = date(DATE_FORMAT, strtotime($dbResults['date_created']));
        $comment->status = $dbResults['status'];
        $comment->released = ($dbResults['released'] == 1) ? true : false;
        return $comment;
    }

    public function save(Comment $comment)
    {
        $db = Registry::get('db');
        if (!empty($comment->commentId)) {
            // Update
            $query = 'UPDATE ' . DB_PREFIX . 'comments SET';
            $query .= ' user_id = :userId, video_id = :videoId, parent_id = :parentId, comments = :comments, date_created = :dateCreated, status = :status, released = :released';
            $query .= ' WHERE comment_id = :commentId';
            $bindParams = array(
                ':commentId' => $comment->commentId,
                ':userId' => $comment->userId,
                ':videoId' => $comment->videoId,
                ':parentId' => (!empty($comment->parentId)) ? $comment->parentId : 0,
                ':comments' => $comment->comments,
                ':dateCreated' => date(DATE_FORMAT, strtotime($comment->dateCreated)),
                ':status' => $comment->status,
                ':released' => (isset($comment->released) && $comment->released === true) ? 1 : 0,
            );
        } else {
            // Create
            $query = 'INSERT INTO ' . DB_PREFIX . 'comments';
            $query .= ' (user_id, video_id, parent_id, comments, date_created, status, released)';
            $query .= ' VALUES (:userId, :videoId, :parentId, :comments, :dateCreated, :status, :released)';
            $bindParams = array(
                ':userId' => $comment->userId,
                ':videoId' => $comment->videoId,
                ':parentId' => (!empty($comment->parentId)) ? $comment->parentId : 0,
                ':comments' => $comment->comments,
                ':dateCreated' => gmdate(DATE_FORMAT),
                ':status' => (!empty($comment->status)) ? $comment->status : 'new',
                ':released' => (isset($comment->released) && $comment->released === true) ? 1 : 0,
            );
        }

        $db->query($query, $bindParams);
        $commentId = (!empty($comment->commentId)) ? $comment->commentId : $db->lastInsertId();
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

    public function getVideoCommentCount($videoId)
    {
        $db = Registry::get('db');
        $sql = 'SELECT COUNT(comment_id) AS count FROM ' . DB_PREFIX . 'comments WHERE video_id = ? AND status = "approved"';
        $result = $db->fetchRow($sql, array($videoId));
        return $result['count'];
    }

    public function getThreadedCommentIds($videoId, $limit, $parentCommentId = 0, $offsetCommentId = 0)
    {
        $db = Registry::get('db');
        $sql = 'SELECT comment_id FROM ' . DB_PREFIX . 'comments ';
        $where = 'video_id = :videoId AND parent_id = :parentCommentId and status = :status';

        $params = array(
            ':videoId' => $videoId,
            ':parentCommentId' => $parentCommentId,
            ':status' => 'approved'
        );

        if (!empty($offsetCommentId)) {
            $params[':offsetCommentId'] = $offsetCommentId;
            $where .= ' AND comment_id > :offsetCommentId';
        }

        $sql .= 'WHERE ' . $where . ' ORDER BY date_created ASC LIMIT ' . (int) $limit;
        $result = $db->fetchAll($sql, $params);
        return Functions::arrayColumn($result, 'comment_id');
    }
}