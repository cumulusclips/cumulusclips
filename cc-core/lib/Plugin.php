<?php


/**********
PLUGIN CODE
**********/



class Custom {

    public function __construct() {
        Plugin::Attach ( 'app.start' , array( $this , 'CustomThing' ) );
        Plugin::Attach ( 'app.start' , array( $this , 'CustomCode' ) );
    }

    static function CustomThing() { echo 'Here';}
    static function CustomCode() { echo 'Again';}

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

        foreach (self::$events[$event_name] as $call_back_method) {
            call_user_func ($call_back_method);
        }
        
    }


    /**
     * Instantiate all plugins and attach their methods to the listener
     */
    static function Init() {

        // Load all installed plugins
        foreach (glob($_SERVER['DOCUMENT_ROOT'] . '/test/*') as $plugin) {

            // Determine name of plugin and plugin class
            $plugin_name = basename ($plugin);
            $class_name = ucfirst ($plugin_name);

            // Load plugin
            include ($plugin . '/plugin.php');

            // Initialize plugin
            new $class_name;

        }

    }


    /**
     * Install a plugin from an archive zip file
     * @param string $plugin_name Name of plugin to be installed
     * @return void Plugin is made available for use and archive is deleted
     */
    static function Install ($plugin_name) {

        // Determine plugin path
        $plugin = $_SERVER['DOCUMENT_ROOT'] . "/test/$plugin_name.zip";

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

Plugin::Install ('custom');
Plugin::Init();
Plugin::Trigger ('app.start');


?>