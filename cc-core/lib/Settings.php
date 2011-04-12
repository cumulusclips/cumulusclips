<?php

class Settings {

    private static $settings;
    protected static $table = 'settings';
    protected static $id_name = 'setting_id';

    /**
     * Load site settings from DB into memory
     */
    public static function LoadSettings() {

        self::$settings = new stdClass();

        // Retrieve all settings from DB and store in object
        $db = Database::GetInstance();
        $query = "SELECT * FROM " . self::$table;
        $result = $db->Query ($query);
        while ($row = $db->FetchObj ($result)) {
            $field = $row->name;
            self::$settings->$field = $row->value;
        }

    }



    /**
     * Retrieve value of site setting
     * @param string $setting_name Name of setting to be retrieved
     * @return string Value of requested setting
     */
    public static function Get ($setting_name) {
        return self::$settings->$setting_name;
    }

}

?>