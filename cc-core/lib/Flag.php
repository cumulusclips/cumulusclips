<?php

class Flag {

    public $found;
    private $db;
    protected static $table = 'flags';
    protected static $id_name = 'flag_id';



    /**
     * Instantiate object
     * @param integer $id ID of record to be instantiated
     * @return object Returns object of class type
     */
    public function  __construct ($id) {
        $this->db = Database::GetInstance();
        if (self::Exist (array (self::$id_name => $id))) {
            $this->Get ($id);
            $this->found = true;
        } else {
            $this->found = false;
        }
    }




    /**
     * Extract values from database and set them to object properties
     * @param integer $id ID of record to be instantiated
     * @return void
     */
    private function Get ($id) {
        $query = 'SELECT * FROM ' . DB_PREFIX . self::$table . ' WHERE ' . self::$id_name . "= $id";
        $result = $this->db->Query ($query);
        $row = $this->db->FetchAssoc ($result);
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }
        Plugin::Trigger ('flag.get');
    }




    /**
     * Check if a record exists matching the given criteria
     * @param array $data Key/Value pairs to use in select criteria i.e. array (field_name => value)
     * @return integer|boolean Returns record ID if record is found or boolean false if not found
     */
    static function Exist ($data) {

        $db = Database::GetInstance();
        $query = 'SELECT ' . self::$id_name . ' FROM ' . DB_PREFIX . self::$table . ' WHERE';

        foreach ($data as $key => $value) {
            $value = $db->Escape ($value);
            $query .= " $key = '$value' AND";
        }

        $query = substr ($query, 0, -4);
        $result = $db->Query ($query);

        if ($db->Count($result) > 0) {
            $row = $db->FetchAssoc ($result);
            return $row[self::$id_name];
        } else {
            return false;
        }

    }




    /**
     * Create a new record using the given criteria
     * @param array $data Key/Value pairs to use as data for new record i.e. array (field_name => value)
     * @return integer Returns the ID of the newly created record
     */
    static function Create ($data) {

        $db = Database::GetInstance();
        $query = 'INSERT INTO ' . DB_PREFIX . self::$table;
        $fields = 'date_created, ';
        $values = 'NOW(), ';

        Plugin::Trigger ('flag.before_create');
        foreach ($data as $_key => $_value) {
            $fields .= "$_key, ";
            $values .= "'" . $db->Escape ($_value) . "', ";
        }

        $fields = substr ($fields, 0, -2);
        $values = substr ($values, 0, -2);
        $query .= " ($fields) VALUES ($values)";
        $db->Query ($query);
        Plugin::Trigger ('flag.create');
        return $db->LastId();

    }




    /**
     * Update current record using the given data
     * @param array $data Key/Value pairs of data to be updated i.e. array (field_name => value)
     * @return void
     */
    public function Update ($data) {

        Plugin::Trigger ('flag.before_update');
        $query = 'UPDATE ' . DB_PREFIX . self::$table . " SET";
        foreach ($data as $_key => $_value) {
            $query .= " $_key = '" . $this->db->Escape ($_value) . "',";
        }

        $query = substr ($query, 0, -1);
        $id_name = self::$id_name;
        $query .= " WHERE $id_name = " . $this->$id_name;
        $this->db->Query ($query);
        $this->Get ($this->$id_name);
        Plugin::Trigger ('flag.update');

    }




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
     * @param string $decision The action to be performed on the record. Possible values are: ban, unban, decline
     * @return void Record and related content are updated
     */
    static function FlagDecision ($id, $type, $decision) {

        $db = Database::GetInstance();
        // Determine what ban action was taken on record
        switch ($decision) {

            ### Content is being banned
            case 'ban':

                switch ($type) {

                    // Set video to 'Banned'
                    case 'video':
                        $query = "UPDATE " . DB_PREFIX . "videos SET status = 7 WHERE video_id = $id";
                        $db->Query ($query);
                        break;

                    // Perform user 'Ban' operations
                    case 'user':

                        // Set videos to 'Banned Chain'
                        $query = "UPDATE " . DB_PREFIX . "videos SET status = 10 WHERE user_id = $id";
                        $db->Query ($query);

                        // Set comments to 'Banned Chain'
                        $query = "UPDATE " . DB_PREFIX . "comments SET status = 'banned_chain' WHERE user_id = $id";
                        $db->Query ($query);

                        // Set user to 'Banned'
                        $query = "UPDATE " . DB_PREFIX . "users SET status = 'banned' WHERE user_id = $id";
                        $db->Query ($query);
                        break;

                    // Set comment to 'Banned'
                    case 'comment':
                        $query = "UPDATE " . DB_PREFIX . "comments SET status = 'banned' WHERE comment_id = $id";
                        $db->Query ($query);
                        break;

                }

                // Update flag requests
                $query = "UPDATE flags SET status = 'approved' WHERE type = '$type' AND id = $id";
                $db->Query ($query);
                break;


            ### Ban decision is being reversed
            case 'unban':

                switch ($type) {

                    // Restore video to 'Approved'
                    case 'video':
                        $query = "UPDATE " . DB_PREFIX . "videos SET status = 6 WHERE video_id = $id";
                        $db->Query ($query);
                        break;

                    // Perform user unban operations
                    case 'user':

                        // Restore videos to 'Approved'
                        $query = "UPDATE " . DB_PREFIX . "videos SET status = 6 WHERE status = 10 AND user_id = $id";
                        $db->Query ($query);

                        // Restore comments to 'Approved'
                        $query = "UPDATE " . DB_PREFIX . "comments SET status = 'approved' WHERE user_id = $id";
                        $db->Query ($query);

                        // Restore user to 'Approved'
                        $query = "UPDATE " . DB_PREFIX . "users SET status = 'active' WHERE user_id = $id";
                        $db->Query ($query);
                        break;

                    // Restore comment to 'Approved'
                    case 'comment':
                        $query = "UPDATE " . DB_PREFIX . "comments SET status = 'approved' WHERE comment_id = $id";
                        $db->Query ($query);
                        break;

                }


            ### Ban request is declined
            case 'decline':
                // Update flag requests
                $query = "UPDATE flags SET status = 'declined' WHERE type = '$type' AND id = $id";
                $db->Query ($query);
                break;

        }   // END decision switch

    }

}

?>