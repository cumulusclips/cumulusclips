<?php

class Plugin {

    private static $events = array();

    /**
     * Add plugin method (code) to specified event in system
     * @param string $event_name The event to attach plugin method to
     * @param function $call_back_method Plugin code to execute when given event is reached
     */
    static function Attach ( $event_name , $call_back_method ) {

        // Create event list if non exist
        if (empty (self::$events[$event_name])) {
            self::$events[$event_name] = array();
        }

        // Add callback to event list
        self::$events[$event_name][] = $call_back_method;

    }




    /**
     * Execute methods (code) attached to specified event
     * @param string $event_name Event for which attached events are fired
     */
    static function Trigger ( $event_name ) {

        // Call plugin methods if any are attached to event
        if (isset (self::$events[$event_name])) {
            foreach (self::$events[$event_name] as $call_back_method) {
                call_user_func ($call_back_method);
            }
        }
        
    }




    /**
     * Instantiate all plugins and attach their methods to the listener
     */
    static function Init() {

        // Retrieve all active plugins
        $active_plugins = self::GetEnabledPlugins();

        // Load all active plugins
        foreach ($active_plugins as $plugin) {

            // Load plugin
            include_once (DOC_ROOT . "/cc-content/plugins/$plugin/$plugin.php");

            // Load plugin and attach it's code to various hooks
            call_user_func (array ($plugin, 'Load'));
            # $plugin::Load();  // Only works on PHP 5.3+

        }

    }




    /**
     * Retrieve a list of valid enabled plugins
     * @return array Returns a list of enabled plugins, any orphaned plugins are disabled
     */
    static function GetEnabledPlugins() {

        $enabled = Settings::Get ('enabled_plugins');
        $enabled = unserialize ($enabled);

        foreach ($enabled as $key => $plugin) {
            $plugin_file = DOC_ROOT . "/cc-content/plugins/$plugin/$plugin.php";
            if (!file_exists ($plugin_file)) {
                unset ($enabled[$key]);
            }
        }
        
        reset ($enabled);
        Settings::Set ('enabled_plugins', serialize ($enabled));
        return $enabled;

    }




    /**
     * Retrieve plugin information
     * @param string $plugin Name of the plugin whose information to retrieve
     * @return object Innstance of stdClass object is returned is developers
     * information
     */
    static function GetPluginInfo ($plugin) {
        return (object) call_user_func (array ($plugin, 'Info'));
    }




    /**
     * Check if a give plugin a valid
     * @param string $plugin Name of the plugin to validate
     * @param boolean $load_plugin_file [optional] Whether to perform a deep
     * validity check, if true the plugin is loaded into memory
     * @return boolean Returns true if the plugin is valid, false otherwise
     */
    static function ValidPlugin ($plugin, $load_plugin_file = true) {

        // Check plugin file exists
        $plugin_file = DOC_ROOT . "/cc-content/plugins/$plugin/$plugin.php";
        if (!file_exists ($plugin_file)) return false;

        // Perform deeper validity check
        if ($load_plugin_file) {

            // Load plugin and check it's info method outputs required data
            include_once ($plugin_file);
            if (method_exists ($plugin, 'Info')) {
                $info = (object) call_user_func (array ($plugin, 'Info'));
                return (!empty ($info->name)) ? true : false;
            } else {
                return false;
            }

        } else {
            return true;
        }
    }

}

?>