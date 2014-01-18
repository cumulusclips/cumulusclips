<?php

class CategoryService extends ServiceAbstract
{
    /**
     * Retrieve list of active categories
     * @return array Returns a list with the category Ids of active categories 
     */
    public function getCategories()
    {
        $db = Registry::get('db');
        $categoryMapper = new CategoryMapper();
        $query = "SELECT category_id FROM " . DB_PREFIX . "categories ORDER BY name ASC";
        $categoryResults = $db->fetchAll($query);
        return $categoryMapper->getCategoriesFromList(
            Functions::arrayColumn($categoryResults, 'category_id')
        );
    }
}