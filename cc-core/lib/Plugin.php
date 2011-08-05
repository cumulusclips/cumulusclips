<?php


/**********
PLUGIN CODE
**********/

class SamplePlugin {

    /**
     * Attach plugin methods to hooks throughout the codebase. Method is called
     * when the plugin system is initialized (cc-core/config/bootstrap.php).
     *
     * It is recommended you put all your attachment calls here for the sake of
     * keeping your sanity. It is possible to attach to hooks later on
     * within your plugin methods, but at the very least attach your
     * Init/Bootstrap method at this point.
     *
     * @example Plugin::Attach ( 'EVENT_NAME' , array( __CLASS__ , 'METHOD_NAME' ) );
     */
    public function Load() {}




    /**
     * Provide information about the plugin
     * @return array Returns an array with information about the plugin.
     * @example return array ('plugin_name' => 'Test Plugin', 'author' => 'CumulusClips.org');
     *      Required items are:
     *          plugin_name - Formal name for the plugin
     *      Optional items are:
     *          author - Person or organization who created plugin
     *          version - Version number of the plugin in 3 place format e.g: 5.1.7
     *          notes - Notes about or description of the plugin to the end user
     *          site - URL where more documentation / information about the plugin can be obtained
     *          Any other custom information can be added for internal use
     */
    static function Info() {}




    /**
     * Output settings page for the plugin if applicable
     * @return string Returns the HTML for the settings page of the plugin, If
     * is ommited then no settings page is displayed
     * 
     * NOTE: This method is called after headers are sent, you will not be able
     * to modify header information with this method
     */
    static function Settings() {}




    /**
     * Perform additional actions required for plugin installation. This method
     * is called when a plugin is activated. This is where you would execute for
     * example any create any database tables or write files, etc.
     *
     * This method is not required and can be ommited. It will only execute if
     * exists during plugin activation.
     */
    static function Install() {}




    /**
     * Revert any additional actions made by the plugin during it's installation.
     * This method is called when a plugin is deactivated or deleted. This is
     * where you would for example remove any database tables or delete files, etc.
     *
     * This method is not required and can be ommited. It will only execute if
     * exists during plugin deactivation or plugin removal.
     */
    static function Uninstall() {}

}







/***********
PLUGIN CLASS
***********/

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
        $active_plugins = self::GetActivePlugins();

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
     * Install a plugin from an archive zip file
     * @param string $plugin_name Name of plugin to be installed
     * @return void Plugin is made available for use and archive is deleted
     */
    static function Install ($plugin_name) {

        // Determine plugin path
        $plugin = DOC_ROOT . '/cc-content/plugins/' . $plugin_name . '.zip';

        // Extract plugin
        $za = new ZipArchive();
        $za->open($plugin);
        $za->extractTo (dirname ($plugin));

        // Remove uneeded zip
        unlink($plugin);

    }




    /**
     * Retrieve a list of valid active plugins
     * @return array Returns a list of active plugins, any orphaned plugins are deactivated
     */
    static function GetActivePlugins() {

        $active = Settings::Get ('active_plugins');
        $active = unserialize ($active);

        foreach ($active as $key => $plugin) {
            $plugin_file = DOC_ROOT . "/cc-content/plugins/$plugin/$plugin.php";
            if (!file_exists ($plugin_file)) {
                unset ($active[$key]);
            }
        }
        
        reset ($active);
        Settings::Set ('active_plugins', serialize ($active));
        return $active;

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
                return (!empty ($info->plugin_name)) ? true : false;
            } else {
                return false;
            }

        } else {
            return true;
        }
    }

}

?>