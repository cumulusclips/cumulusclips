<?php

class FlagService {

    public $found;
    private $db;
    protected static $table = 'flags';
    protected static $id_name = 'flag_id';




    /**
     * Delete a record
     * @param integer $id ID of record to be deleted
     * @return void Record is deleted from database
     */
    static function Delete ($id) {
        $db = Database::GetInstance();
        Plugin::Trigger ('flag.delete');
        $query = "DELETE FROM " . DB_PREFIX . self::$table . " WHERE " . self::$id_name . " = $id";
        $db->Query ($query);
    }




    /**
     * Perform flag related action on a record
     * @param integer $id The id of the record being updated
     * @param string $type Type of record being updated. Possible values are: video, user, comment
     * @param boolean $decision The action to be performed on the record. True bans, False declines the flag
     * @return void All flags raised against record are updated
     */
    static function FlagDecision ($id, $type, $decision) {

        $db = Database::GetInstance();
        if ($decision) {
            // Content is being banned - Update flag requests
            $query = "UPDATE " . DB_PREFIX . "flags SET status = 'approved' WHERE type = '$type' AND id = $id";
            $db->Query ($query);
        } else {
            // Ban request is declined - Update flag requests
            $query = "UPDATE " . DB_PREFIX . "flags SET status = 'declined' WHERE type = '$type' AND id = $id";
            $db->Query ($query);
        } 

    }

}