<?php

class CategoryService extends ServiceAbstract
{
    public $found;
    private $db;
    protected static $table = 'categories';
    protected static $id_name = 'cat_id';

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
}