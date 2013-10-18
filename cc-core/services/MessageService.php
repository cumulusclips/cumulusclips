<?php

class MessageService extends ServiceAbstract
{
    /**
     * Delete a record
     * @param integer $id ID of record to be deleted
     * @return void Record is deleted from database
     */
    public function Delete ($id) {
        $db = Database::GetInstance();
        Plugin::Trigger ('message.delete');
        $query = "DELETE FROM " . DB_PREFIX . self::$table . " WHERE " . self::$id_name . " = $id";
        $db->Query ($query);
    }
}