<?php

class PageMapper extends MapperAbstract
{
    public function getPageById($pageId)
    {
        return $this->getPageByCustom(array('page_id' => $pageId));
    }
    
    public function getPageBySlug($slug)
    {
        return $this->getPageByCustom(array('slug' => $slug));
    }
    
    public function getPageByCustom(array $params)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'pages WHERE ';
        
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

    protected function _map($dbResults)
    {
        $page = new Page();
        $page->pageId = $dbResults['page_id'];
        $page->title = $dbResults['title'];
        $page->content = $dbResults['content'];
        $page->slug = $dbResults['slug'];
        $page->layout = $dbResults['layout'];
        $page->status = $dbResults['status'];
        $page->dateCreated = date(DATE_FORMAT, strtotime($dbResults['date_created']));
        return $page;
    }

    public function save(Page $page)
    {
        $db = Registry::get('db');
        if (!empty($page->pageId)) {
            // Update
            $query = 'UPDATE ' . DB_PREFIX . 'pages SET';
            $query .= ' title = :title, content = :content, slug = :slug, layout = :layout, status = :status, date_created = :dateCreated';
            $query .= ' WHERE page_id = :pageId';
            $bindParams = array(
                ':pageId' => $page->pageId,
                ':title' => $page->title,
                ':content' => (!empty($page->content)) ? $page->content : '',
                ':slug' => $page->slug,
                ':layout' => $page->layout,
                ':status' => $page->status,
                ':dateCreated' => date(DATE_FORMAT, strtotime($page->dateCreated))
            );
        } else {
            // Create
            $query = 'INSERT INTO ' . DB_PREFIX . 'pages';
            $query .= ' (title, content, slug, layout, status, date_created)';
            $query .= ' VALUES (:title, :content, :slug, :layout, :status, :dateCreated)';
            $bindParams = array(
                ':title' => $page->title,
                ':content' => (!empty($page->content)) ? $page->content : '',
                ':slug' => $page->slug,
                ':layout' => $page->layout,
                ':status' => (!empty($page->status)) ? $page->status : 'draft',
                ':dateCreated' => gmdate(DATE_FORMAT)
            );
        }
            
        $db->query($query, $bindParams);
        $pageId = (!empty($page->pageId)) ? $page->pageId : $db->lastInsertId();
        return $pageId;
    }
    
    
    public function getPagesFromList(array $pageIds)
    {
        $pageList = array();
        if (empty($pageIds)) return $pageList;
        
        $db = Registry::get('db');
        $inQuery = implode(',', array_fill(0, count($pageIds), '?'));
        $sql = 'SELECT * FROM ' . DB_PREFIX . 'pages WHERE page_id IN (' . $inQuery . ')';
        $result = $db->fetchAll($sql, $pageIds);

        foreach($result as $pageRecord) {
            $pageList[] = $this->_map($pageRecord);
        }
        return $pageList;
    }
    
    public function delete($pageId)
    {
        $db = Registry::get('db');
        $query = 'DELETE FROM ' . DB_PREFIX . 'pages WHERE page_id = :pageId';
        $db->query($query, array(':pageId' => $pageId));
    }
}