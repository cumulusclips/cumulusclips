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

    private static $_enabledPlugins = array();
    private static $_installedPlugins = array();

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
                $args = array_slice(func_get_args(), 1);
                $value = call_user_func_array($callbackMethod, $args);
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
        // Retrieve all enabled plugins
        $installedPlugins = json_decode(Settings::get('installed_plugins'));
        $enabledPlugins = json_decode(Settings::get('enabled_plugins'));

        // Load enabled plugins and attach their code to code to corresponding hooks
        $pluginsWereDisabled = false;
        foreach ($enabledPlugins as $key => $pluginName) {
            if (self::isPluginValid($pluginName)) {
                $plugin = self::getPlugin($pluginName);
                $plugin->load();
                Language::loadPluginLanguage($pluginName);
            } else {
                $pluginsWereDisabled = true;
                unset($enabledPlugins[$key]);
            }
        }
        reset($enabledPlugins);
        self::$_installedPlugins = $installedPlugins;
        self::$_enabledPlugins = $enabledPlugins;

        // Update enabled plugin list if any plugins were enabled but were not valid
        if ($pluginsWereDisabled) {
            Settings::set('enabled_plugins', json_encode($enabledPlugins));
        }
    }

    /**
     * Instantiates plugin for given theme
     *
     * @param string $themeName Name of the theme whose plugin will be loaded
     * @throws \Exception Thrown if theme plugin does not extend PluginAbstract
     */
    public static function loadThemePlugin($themeName)
    {
        // Load theme plugin if defined
        $pluginPath = THEMES_DIR . '/' . $themeName . '/plugin/' . $themeName . '.php';
        if (file_exists($pluginPath)) {

            // Load theme plugin files
            include_once($pluginPath);

            // Initialize theme plugin
            $themePlugin = new $themeName();
            if (!$themePlugin instanceof PluginAbstract) throw new Exception('Plugins must extend PluginAbstract');
            $themePlugin->load();
        }
    }

    /**
     * Check if plugin is valid
     * @param string $pluginName Name of the plugin to validate
     * @return boolean Returns true if the plugin is valid, false otherwise
     */
    public static function isPluginValid($pluginName)
    {
        // Check plugin file exists
        $pluginFile = DOC_ROOT . "/cc-content/plugins/$pluginName/$pluginName.php";
        if (!file_exists($pluginFile)) {
            return false;
        }

        // Load plugin
        include_once($pluginFile);
        if (!class_exists($pluginName, false)) return false;
        $plugin = new $pluginName();

        // Verify plugin adheres to plugin API
        if (!$plugin instanceof PluginAbstract) return false;
        return true;
    }

    /**
     * Load and retrieve instance of given plugin
     * @param string $pluginName Name of plugin to be retireved
     * @return PluginAbstract Returns instance of given plugin
     */
    public static function getPlugin($pluginName)
    {
        include_once(DOC_ROOT . '/cc-content/plugins/' . $pluginName . '/' . $pluginName . '.php');
        return new $pluginName();
    }

    /**
     * Checks if given plugin has a settings method
     * @param PluginAbstract $plugin Plugin to be checked
     * @return boolean Returns true if settings method exists, false otherwise
     */
    public static function hasSettingsMethod(PluginAbstract $plugin)
    {
        $pluginReflection = new ReflectionClass($plugin);
        $pluginReflectionMethod = $pluginReflection->getMethod('settings');
        return ($pluginReflectionMethod->getDeclaringClass()->name == 'PluginAbstract') ? false : true;
    }

    /**
     * Retrieves a list of plugins which have been installed
     * @return array Returns list of installed plugin names
     */
    public static function getInstalledPlugins()
    {
        return json_decode(Settings::get('installed_plugins'));
    }

    /**
     * Retrieve a list of valid enabled plugins
     * @return array Returns a list of enabled plugins, any orphaned plugins are disabled
     */
    public static function getEnabledPlugins()
    {
        return self::$_enabledPlugins;
    }

    /**
     * Checks if a given plugin is enabled
     * @param string $pluginName Name of plugin to be checked
     * @return boolean Returns true if plugin is enabled, false otherwise
     */
    public static function isPluginEnabled($pluginName)
    {
        return (in_array($pluginName, self::$_enabledPlugins)) ? true : false;
    }

    /**
     * Checks if given plugin is installed
     * @param string $pluginName Name of plugin to be checked
     * @return boolean Returns true if plugin is installed, false otherwise
     */
    public static function isPluginInstalled($pluginName)
    {
        return (in_array($pluginName, self::$_installedPlugins)) ? true : false;
    }

    /**
     * Installs and enables given plugin
     * @param string $pluginName Name of plugin to be installed
     */
    public static function installPlugin($pluginName)
    {
        $plugin = self::getPlugin($pluginName);
        $plugin->install();
        self::$_installedPlugins[] = $pluginName;
        Settings::set('installed_plugins', json_encode(self::$_installedPlugins));
        self::enablePlugin($pluginName);
    }

    /**
     * Disables and uninstalls given plugin
     * @param string $pluginName Name of plugin to be uninstalled
     */
    public static function uninstallPlugin($pluginName)
    {
        $plugin = self::getPlugin($pluginName);
        $plugin->uninstall();
        $key = array_search($pluginName, self::$_installedPlugins);
        unset(self::$_installedPlugins[$key]);
        Settings::set('installed_plugins', json_encode(self::$_installedPlugins));
        self::disablePlugin($pluginName);
    }

    /**
     * Marks given plugin as enabled
     * @param string $pluginName Name of plugin to be enabled
     */
    public static function enablePlugin($pluginName)
    {
        self::$_enabledPlugins[] = $pluginName;
        Settings::set('enabled_plugins', json_encode(self::$_enabledPlugins));
    }

    /*
     * Marks given plugin as disabled
     * @param string $pluginName Name of plugin to be disabled
     */
    public static function disablePlugin($pluginName)
    {
        $key = array_search($pluginName, self::$_enabledPlugins);
        unset(self::$_enabledPlugins[$key]);
        Settings::set('enabled_plugins', json_encode(self::$_enabledPlugins));
    }
}