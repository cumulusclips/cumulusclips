<?php


/**********
PLUGIN CODE
**********/



class Sample_Plugin {

    public function Load() {
        // Syntax: Plugin::Attach ( 'EVENT_NAME' , array( 'PLUGIN_NAME' , 'METHOD_NAME' ) );
        Plugin::Attach ( 'app.start' , array( 'Sample_Plugin' , 'CustomThing' ) );
        Plugin::Attach ( 'app.start' , array( 'Sample_Plugin' , 'CustomCode' ) );
    }

    static function CustomThing() { echo 'Here';}
    static function CustomCode() { echo 'Again';}
    static function Info() { /* Info Array */ }

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
        $active_plugins = Settings::Get ('active_plugins');
        $active_plugins = unserialize ($active_plugins);

        // Load all active plugins
        foreach ($active_plugins as $plugin) {

            // Load plugin
            include (DOC_ROOT . '/cc-content/plugins/' . $plugin . '/plugin.php');

            // Load plugin and attach it's code to various hooks
            $plugin::Load();

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

}





/**********
SYSTEM CODE
**********/

//Plugin::Install ('Sample_Plugin');
//Plugin::Init();
//Plugin::Trigger ('app.start');
//

?>