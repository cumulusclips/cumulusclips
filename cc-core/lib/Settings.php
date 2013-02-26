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
    public static function LoadSettings()
    {
        self::$settings = new stdClass();

        // Retrieve all settings from DB and store in object
        $db = Database::GetInstance();
        $query = "SELECT * FROM " . DB_PREFIX . self::$table;
        $result = $db->Query ($query);
        while ($row = $db->FetchObj ($result)) {
            $field = $row->name;
            self::$settings->$field = $row->value;
        }
    }

    /**
     * Retrieve value of site setting
     * @param string $setting_name Name of setting to be retrieved
     * @return string|boolean Value of requested setting or false if it doesn't exist
     */
    public static function Get($setting_name)
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
    public static function Set($settingName, $value)
    {
        $db = Database::GetInstance();
        $cleanSettingName = $db->Escape($settingName);
        $cleanValue = $db->Escape($value);
        if (self::Get($settingName)) {
            $query = "UPDATE " . DB_PREFIX . self::$table . " SET value = '$cleanValue' WHERE name = '$cleanSettingName'";
        } else {
            $query = "INSERT INTO " . DB_PREFIX . self::$table . " (name, value) VALUES ('$cleanSettingName', '$cleanValue')";
        }
        $db->Query ($query);
        self::$settings->$settingName = $value;
    }
    
    /**
     * Remove a site setting
     * @param string $settingName Name of the setting to be removed
     * @return void Setting is removed from DB and object in memory
     */
    public static function Remove($settingName)
    {
        unset(self::$settings->$settingName);
        $db = Database::GetInstance();
        $settingName = $db->Escape($settingName);
        $query = "DELETE FROM " . DB_PREFIX . self::$table . " WHERE name = '$settingName'";
        $db->Query($query);
    }
}