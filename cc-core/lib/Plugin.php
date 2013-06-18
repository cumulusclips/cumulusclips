<?php

class Plugin
{
    /**
     * @var array List of events hooks with plugins attached to them
     */
    private static $_events = array();
    
    /**
     * @var array List of filter hooks with plugins attached to them 
     */
    private static $_filters = array();

    /**
     * Add plugin method (code) to specified event in system
     * @deprecated as of v2.0, Use self::attachEvent instead
     */
    public static function Attach($eventName, $callbackMethod)
    {
        self::attachEvent($eventName, $callbackMethod);
    }
    
    /**
     * Add plugin method (code) to specified event in system
     * @param string $eventName The event to attach plugin method to
     * @param function $callbackMethod Plugin code to execute when given event is reached
     */
    public static function attachEvent($eventName, $callbackMethod)
    {
        // Create event list if non exist
        if (empty(self::$_events[$eventName])) {
            self::$_events[$eventName] = array();
        }

        // Add callback to event list
        self::$_events[$eventName][] = $callbackMethod;
    }

    /**
     * Add plugin method (code) to specified filter hook in system
     * @param string $filterName The filter hook to attach plugin method to
     * @param function $callbackMethod Plugin code to execute when given filter hook is reached
     */
    public static function attachFilter($filterName, $callbackMethod)
    {
        // Create filter list if non exist
        if (empty(self::$_filters[$filterName])) {
            self::$_filters[$filterName] = array();
        }

        // Add callback to filter list
        self::$_filters[$filterName][] = $callbackMethod;
    }

    /**
     * Execute methods (code) attached to specified event
     * @deprecated as of v2.0, use self::triggerEvent instead
     */
    public static function Trigger($eventName)
    {
        self::triggerEvent($eventName);
    }
    
    /**
     * Execute methods (code) attached to specified event
     * @param string $eventName Event for which attached events are fired
     */
    public static function triggerEvent($eventName)
    {
        // Call plugin methods if any are attached to event
        if (!empty(self::$_events[$eventName])) {
            foreach (self::$_events[$eventName] as $callbackMethod) {
                $args = array_slice(func_get_args(), 1);
                call_user_func_array($callbackMethod, $args);
            }
        }
    }

    /**
     * Execute methods (code) attached to specified filter hook
     * @param string $filterName Name of filter hook for which attached plugins are fired
     * @param mixed $value Value being passed through filter
     * @return mixed Filtered value is returned
     * @throws Exception If attached plugin method returns null value
     */
    public static function triggerFilter($filterName, $value)
    {
        // Call plugin methods if any are attached to filter hook
        if (!empty(self::$_filters[$filterName])) {
            foreach (self::$_filters[$filterName] as $callbackMethod) {
                $value = call_user_func($callbackMethod, $value);
                if ($value === null) {
                    throw new Exception('Return type for filter methods cannot be null');
                }
            }
        }
        return $value;
    }

    /**
     * Instantiate all plugins and attach their methods to the listener
     */
    public static function init()
    {
        // Retrieve all active plugins
        $active_plugins = self::GetEnabledPlugins();

        // Load all active plugins
        foreach ($active_plugins as $plugin) {

            // Load plugin
            include_once(DOC_ROOT . "/cc-content/plugins/$plugin/$plugin.php");

            // Load plugin and attach it's code to various hooks
            call_user_func(array($plugin, 'Load'));
        }
    }

    /**
     * Retrieve a list of valid enabled plugins
     * @return array Returns a list of enabled plugins, any orphaned plugins are disabled
     */
    public static function GetEnabledPlugins()
    {
        $enabled = Settings::Get('enabled_plugins');
        $enabled = unserialize($enabled);

        foreach ($enabled as $key => $plugin) {
            $plugin_file = DOC_ROOT . "/cc-content/plugins/$plugin/$plugin.php";
            if (!file_exists($plugin_file)) {
                unset($enabled[$key]);
            }
        }
        
        reset($enabled);
        Settings::Set('enabled_plugins', serialize($enabled));
        return $enabled;
    }

    /**
     * Retrieve plugin information
     * @param string $plugin Name of the plugin whose information to retrieve
     * @return object Innstance of stdClass object is returned is developers
     * information
     */
    public static function GetPluginInfo($plugin)
    {
        return (object) call_user_func(array($plugin, 'Info'));
    }

    /**
     * Check if a give plugin a valid
     * @param string $plugin Name of the plugin to validate
     * @param boolean $loadPluginFile (optional) Whether to perform a deep
     * validity check, if true the plugin is loaded into memory
     * @return boolean Returns true if the plugin is valid, false otherwise
     */
    public static function ValidPlugin($plugin, $loadPluginFile = true)
    {
        // Check plugin file exists
        $plugin_file = DOC_ROOT . "/cc-content/plugins/$plugin/$plugin.php";
        if (!file_exists($plugin_file)) return false;

        // Perform deeper validity check
        if ($loadPluginFile) {

            // Load plugin and check it's info method outputs required data
            include_once($plugin_file);
            if (method_exists($plugin, 'Info')) {
                $info = (object) call_user_func(array($plugin, 'Info'));
                return (!empty($info->name)) ? true : false;
            } else {
                return false;
            }

        } else {
            return true;
        }
    }
}