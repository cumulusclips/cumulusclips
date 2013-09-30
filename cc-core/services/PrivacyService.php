<?php

class PrivacyService extends ServiceAbstract
{
    public $found;
    private $db;
    protected static $table = 'privacy';
    protected static $id_name = 'privacy_id';

    /**
     * Delete a record
     * @param integer $id ID of record to be deleted
     * @return void Record is deleted from database
     */
    public static function delete($id)
    {
        $db = Database::GetInstance();
        Plugin::Trigger ('privacy.delete');
        $query = "DELETE FROM " . DB_PREFIX . self::$table . " WHERE " . self::$id_name . " = $id";
        $db->Query ($query);
    }

    /**
     * Verify if user accepts message type
     * @param string $message_type The message type to check
     * @return boolean Returns true if user accepts message type, false otherwise
     */
    public function optCheck($message_type)
    {
        if ($this->$message_type == '1') {
            return true;
        } else {
            return false;
        }
    }
}