<?php

class Settings
{
    /**
     * @var stdClass Object containing all values from settings DB table 
     */
    private static $settings;
    
    /**
     * @var string Name of table in the database  
     */
    protected static $table = 'settings';
    
    /**
     * @var string Name of the primary key column in the database table 
     */
    protected static $id_name = 'setting_id';

    /**
     * Load site settings from DB into memory
     */
    public static function loadSettings()
    {
        self::$settings = new stdClass();

        // Retrieve all settings from DB and store in object
        $db = Registry::get('db');
        $query = "SELECT * FROM " . DB_PREFIX . self::$table;
        $results = $db->fetchAll($query, array(), PDO::FETCH_OBJ);
        foreach ($results as $row) {
            $field = $row->name;
            self::$settings->$field = $row->value;
        }
    }

    /**
     * Retrieve value of site setting
     * @param string $setting_name Name of setting to be retrieved
     * @return string|boolean Value of requested setting or false if it doesn't exist
     */
    public static function get($setting_name)
    {
        if (isset(self::$settings->$setting_name)) {
            return self::$settings->$setting_name;
        } else {
            return false;
        }
    }

    /**
     * Update (or create if non-exist) the value of site setting
     * @param string $settingName Name of the setting to be updated
     * @param mixed $value Value to be assigned to the setting
     * @return void Setting is updated or created in DB and object as well
     */
    public static function set($settingName, $value)
    {
        $db = Registry::get('db');
        if (self::Get($settingName)) {
            $query = "UPDATE " . DB_PREFIX . self::$table . " SET value = :settingValue WHERE name = :settingName";
        } else {
            $query = "INSERT INTO " . DB_PREFIX . self::$table . " (name, value) VALUES (:settingName, :settingValue)";
        }
        $db->query($query, array(':settingValue' => $value, ':settingName' => $settingName));
        self::$settings->$settingName = $value;
    }
    
    /**
     * Remove a site setting
     * @param string $settingName Name of the setting to be removed
     * @return void Setting is removed from DB and object in memory
     */
    public static function remove($settingName)
    {
        unset(self::$settings->$settingName);
        $db = Registry::get('db');
        $query = "DELETE FROM " . DB_PREFIX . self::$table . " WHERE name = ?";
        $db->query($query, array($settingName));
    }
}