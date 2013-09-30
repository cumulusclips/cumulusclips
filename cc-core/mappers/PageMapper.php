<?php

class PageMapper extends MapperAbstract
{
    public function getPageById($pageId)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'pages WHERE pageId = :pageId';
        $dbResults = $db->fetchRow($query, array(':pageId' => $pageId));
        if ($db->rowCount() == 1) {
            return $this->_map($dbResults);
        } else {
            return false;
        }
    }

    protected function _map($dbResults)
    {
        $page = new Page();
        $page->pageId = $dbResults['pageId'];
        $page->title = $dbResults['title'];
        $page->content = $dbResults['content'];
        $page->slug = $dbResults['slug'];
        $page->layout = $dbResults['layout'];
        $page->status = $dbResults['status'];
        $page->dateCreated = date(DATE_FORMAT, strtotime($dbResults['dateCreated']));
        return $page;
    }

    public function save(Page $page)
    {
        $page = Plugin::triggerFilter('video.beforeSave', $page);
        $db = Registry::get('db');
        if (!empty($page->pageId)) {
            // Update
            Plugin::triggerEvent('video.update', $page);
            $query = 'UPDATE ' . DB_PREFIX . 'pages SET';
            $query .= ' title = :title, content = :content, slug = :slug, layout = :layout, status = :status, dateCreated = :dateCreated';
            $query .= ' WHERE pageId = :pageId';
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
            Plugin::triggerEvent('video.create', $page);
            $query = 'INSERT INTO ' . DB_PREFIX . 'pages';
            $query .= ' (title, content, slug, dateCreated, layout, status, dateCreated)';
            $query .= ' VALUES (:title, :content, :slug, :dateCreated, :layout, :status, :dateCreated)';
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
        Plugin::triggerEvent('video.save', $pageId);
        return $pageId;
    }
}