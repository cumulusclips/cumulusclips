<?php

class MessageService extends ServiceAbstract
{
    public $found;
    private $db;
    protected static $table = 'messages';
    protected static $id_name = 'message_id';

    /**
     * Delete a record
     * @param integer $id ID of record to be deleted
     * @return void Record is deleted from database
     */
    static function Delete ($id) {
        $db = Database::GetInstance();
        Plugin::Trigger ('message.delete');
        $query = "DELETE FROM " . DB_PREFIX . self::$table . " WHERE " . self::$id_name . " = $id";
        $db->Query ($query);
    }
}