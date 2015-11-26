<?php

class Settings
{
    /**
     * @var stdClass Object containing all values from settings DB table 
     */
    protected static $_settings;

    /**
     * Load site settings from DB into memory
     */
    public static function loadSettings()
    {
        self::$_settings = new stdClass();

        // Retrieve all settings from DB and store in object
        $db = Registry::get('db');
        $query = "SELECT * FROM " . DB_PREFIX . 'settings';
        $results = $db->basicQuery($query);
        foreach ($results as $row) {
            $field = $row['name'];
            self::$_settings->$field = $row['value'];
        }
    }

    /**
     * Retrieve value of site setting
     * @param string $setting_name Name of setting to be retrieved
     * @return string|boolean Value of requested setting or boolean false if it doesn't exist
     */
    public static function get($setting_name)
    {
        if (isset(self::$_settings->$setting_name)) {
            return self::$_settings->$setting_name;
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
        if (self::get($settingName) !== false) {
            $query = "UPDATE " . DB_PREFIX . "settings SET value = :settingValue WHERE name = :settingName";
        } else {
            $query = "INSERT INTO " . DB_PREFIX . "settings (name, value) VALUES (:settingName, :settingValue)";
        }
        $db->query($query, array(':settingValue' => $value, ':settingName' => $settingName));
        self::$_settings->$settingName = $value;
    }
    
    /**
     * Remove a site setting
     * @param string $settingName Name of the setting to be removed
     * @return void Setting is removed from DB and object in memory
     */
    public static function remove($settingName)
    {
        unset(self::$_settings->$settingName);
        $db = Registry::get('db');
        $query = "DELETE FROM " . DB_PREFIX . "settings WHERE name = ?";
        $db->query($query, array($settingName));
    }
}