<?php

class Privacy {

    public $found;
    private $db;
    protected static $table = 'privacy';
    protected static $id_name = 'privacy_id';




    /**
     * Delete a record
     * @param integer $id ID of record to be deleted
     * @return void Record is deleted from database
     */
    static function Delete ($id) {
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
    public function OptCheck ($message_type) {
        if ($this->$message_type == '1') {
            return true;
        } else {
            return false;
        }
    }




    /**
     * Retrieve privacy object using user id
     * @param integer $user_id User id for user being looked up
     * @return object Returns Privacy object if $user_id was found, boolean false otherwise
     */
    static function LoadByUser ($user_id) {
        $privacy_id = self::Exist (array ('user_id' => $user_id));
        if ($privacy_id) {
            return new self ($privacy_id);
        } else {
            return false;
        }
    }

}