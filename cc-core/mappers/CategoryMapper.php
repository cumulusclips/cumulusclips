<?php

class CategoryMapper extends MapperAbstract
{
    public function getCategoryById($categoryId)
    {
        return $this->getCategoryByCustom(array('category_id' => $categoryId));
    }
    
    public function getCategoryBySlug($slug)
    {
        return $this->getCategoryByCustom(array('slug' => $slug));
    }

    public function getCategoryByCustom(array $params)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'categories WHERE ';
        
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
    
    public function getMultipleCategoriesByCustom(array $params)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'categories WHERE ';
        
        $queryParams = array();
        foreach ($params as $fieldName => $value) {
            $query .= "$fieldName = :$fieldName AND ";
            $queryParams[":$fieldName"] = $value;
        }
        $query = preg_replace('/\sAND\s$/', '', $query);
        $dbResults = $db->fetchAll($query, $queryParams);
        
        $categoryList = array();
        foreach($dbResults as $record) {
            $categoryList[] = $this->_map($record);
        }
        return $categoryList;
    }

    protected function _map($dbResults)
    {
        $category = new Category();
        $category->categoryId = $dbResults['category_id'];
        $category->name = $dbResults['name'];
        $category->slug = $dbResults['slug'];
        return $category;
    }

    public function save(Category $category)
    {
        $db = Registry::get('db');
        if (!empty($category->categoryId)) {
            // Update
            $query = 'UPDATE ' . DB_PREFIX . 'categories SET';
            $query .= ' name = :name, slug = :slug';
            $query .= ' WHERE category_id = :categoryId';
            $bindParams = array(
                ':categoryId' => $category->categoryId,
                ':name' => $category->name,
                ':slug' => $category->slug
            );
        } else {
            // Create
            $query = 'INSERT INTO ' . DB_PREFIX . 'categories';
            $query .= ' (name, slug)';
            $query .= ' VALUES (:name, :slug)';
            $bindParams = array(
                ':name' => $category->name,
                ':slug' => $category->slug
            );
        }
            
        $db->query($query, $bindParams);
        $categoryId = (!empty($category->categoryId)) ? $category->categoryId : $db->lastInsertId();
        return $categoryId;
    }
    
    public function getCategoriesFromList(array $categoryIds)
    {
        $categoryList = array();
        if (empty($categoryIds)) return $categoryList;
        
        $db = Registry::get('db');
        $inQuery = implode(',', array_fill(0, count($categoryIds), '?'));
        $sql = 'SELECT * FROM ' . DB_PREFIX . 'categories WHERE category_id IN (' . $inQuery . ')';
        $result = $db->fetchAll($sql, $categoryIds);

        foreach($result as $categoryRecord) {
            $categoryList[] = $this->_map($categoryRecord);
        }
        return $categoryList;
    }
    
    /**
     * Delete a category
     * @param integer $categoryId ID of category to be deleted
     * @return void Category is deleted from system
     */
    public function delete($categoryId)
    {
        $db = Registry::get('db');
        $query = 'DELETE FROM ' . DB_PREFIX . 'categories WHERE category_id = :categoryId';
        $db->query($query, array(':categoryId' => $categoryId));
    }
}