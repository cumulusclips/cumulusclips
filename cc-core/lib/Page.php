<?php

class Page {

    public $found;
    private $db;
    protected static $table = 'pages';
    protected static $id_name = 'page_id';
    protected static $reserved = array (
        'videos',
        'members',
        'myaccount',
        'contact',
        'system-error',
        'nofound',
        'register',
        'login',
        'logout',
        'activate',
        'comments',
        'opt-out',
        'search',
        'actions',
        'feed',
        'page',
        'video-sitemap',
        'language'
    );




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
     ** @return void DB record's fields are loaded into object properties
     */
    private function Get ($id) {
        $query = 'SELECT * FROM ' . DB_PREFIX . self::$table . ' WHERE ' . self::$id_name . "= $id";
        $result = $this->db->Query ($query);
        $row = $this->db->FetchAssoc ($result);
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }
        $this->date_created = Functions::GmtToLocal ($this->date_created);
        Plugin::Trigger ('page.get');
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
        $values = "'" . gmdate (DATE_FORMAT) . "', ";

        Plugin::Trigger ('page.before_create');
        foreach ($data as $_key => $_value) {
            $fields .= "$_key, ";
            $values .= "'" . $db->Escape ($_value) . "', ";
        }

        $fields = substr ($fields, 0, -2);
        $values = substr ($values, 0, -2);
        $query .= " ($fields) VALUES ($values)";
        $db->Query ($query);
        Plugin::Trigger ('page.create');
        return $db->LastId();

    }




    /**
     * Update current record using the given data
     * @param array $data Key/Value pairs of data to be updated i.e. array (field_name => value)
     * @return void Record is updated in DB
     */
    public function Update ($data) {

        Plugin::Trigger ('page.before_update');
        $query = 'UPDATE ' . DB_PREFIX . self::$table . " SET";
        foreach ($data as $_key => $_value) {
            $query .= " $_key = '" . $this->db->Escape ($_value) . "',";
        }

        $query = substr ($query, 0, -1);
        $id_name = self::$id_name;
        $query .= " WHERE $id_name = " . $this->$id_name;
        $this->db->Query ($query);
        $this->Get ($this->$id_name);
        Plugin::Trigger ('page.update');

    }




    /**
     * Delete a record
     * @param integer $id ID of record to be deleted
     * @return void Record is deleted from database
     */
    static function Delete ($id) {
        $db = Database::GetInstance();
        Plugin::Trigger ('page.delete');
        $query = "DELETE FROM " . DB_PREFIX . self::$table . " WHERE " . self::$id_name . " = $id";
        $db->Query ($query);
    }

    
    
    
    /**
     * Check if slug is reserved by system
     * @param string $slug The slug to check if reserved
     * @return boolean Returns true if slug is reserved, false otherwise
     */
    static function IsReserved ($slug) {
        return in_array ($slug, self::$reserved);
    }




    /**
     * Check if slug is available
     * @param string $slug The slug to check if available
     * @return string Returns available version of requested slug
     */
    static function GetAvailableSlug ($slug) {
        $count = 1;
        $slug_check = $slug;
        while (self::IsReserved ($slug_check) || self::Exist (array ('slug' => $slug_check))) {
            $count++;
            $slug_check = "$slug-$count";
        }
        return $slug_check;
    }

}

?>