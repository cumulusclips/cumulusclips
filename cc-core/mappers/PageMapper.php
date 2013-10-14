<?php

class PageMapper extends MapperAbstract
{
    public function getPageById($pageId)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'pages WHERE page_id = :pageId';
        $dbResults = $db->fetchRow($query, array(':pageId' => $pageId));
        if ($db->rowCount() == 1) {
            return $this->_map($dbResults);
        } else {
            return false;
        }
    }
    
    public function getPageBySlug($slug)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'pages WHERE slug = :slug';
        $dbResults = $db->fetchRow($query, array(':slug' => $slug));
        if ($db->rowCount() == 1) {
            return $this->_map($dbResults);
        } else {
            return false;
        }
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
        $query = rtrim($query, ' AND ');
        
        $dbResults = $db->fetchRow($query, $queryParams);
        if ($db->rowCount() == 1) {
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
        $page = Plugin::triggerFilter('page.beforeSave', $page);
        $db = Registry::get('db');
        if (!empty($page->pageId)) {
            // Update
            Plugin::triggerEvent('page.update', $page);
            $query = 'UPDATE ' . DB_PREFIX . 'pages SET';
            $query .= ' title = :title, content = :content, slug = :slug, layout = :layout, status = :status, date_created = :dateCreated';
            $query .= ' WHERE page_id = :pageId';
            $bindParams = array(
                ':pageId' => $page->pageId,
                ':title' => $page->title,
                ':content' => (!empty($page->content)) ? $page->content : null,
                ':slug' => $page->slug,
                ':layout' => $page->layout,
                ':status' => $page->status,
                ':dateCreated' => date(DATE_FORMAT, strtotime($page->dateCreated))
            );
        } else {
            // Create
            Plugin::triggerEvent('page.create', $page);
            $query = 'INSERT INTO ' . DB_PREFIX . 'pages';
            $query .= ' (title, content, slug, layout, status, date_created)';
            $query .= ' VALUES (:title, :content, :slug, :layout, :status, :dateCreated)';
            $bindParams = array(
                ':title' => $page->title,
                ':content' => (!empty($page->content)) ? $page->content : null,
                ':slug' => $page->slug,
                ':layout' => $page->layout,
                ':status' => (!empty($page->status)) ? $page->status : 'draft',
                ':dateCreated' => gmdate(DATE_FORMAT)
            );
        }
            
        $db->query($query, $bindParams);
        $pageId = (!empty($page->pageId)) ? $page->pageId : $db->lastInsertId();
        Plugin::triggerEvent('page.save', $pageId);
        return $pageId;
    }
}