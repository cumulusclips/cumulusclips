<?php

class CategoryService extends ServiceAbstract
{
    /**
     * Delete a record
     * @param integer $id ID of record to be deleted
     * @return void Record is deleted from database
     */
    static function delete($id)
    {
        $db = Database::GetInstance();
        Plugin::Trigger('category.delete');
        $query = "DELETE FROM " . DB_PREFIX . self::$table . " WHERE " . self::$id_name . " = $id";
        $db->Query ($query);
    }
    
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
        return $categoryMapper->getMultipleCategoriesById(
            Functions::flattenArray($categoryResults, 'category_id')
        );
    }
}