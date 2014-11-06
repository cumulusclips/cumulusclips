<?php

abstract class PluginAbstract
{
    public $enabled = true;
    
    /**
     * @var string Name of plugin 
     */
    public $name = 'Plugin Name not available';
    
    /**
     * @var string Description of plugin 
     */
    public $description = '';
    
    /**
     * @var string Name of plugin author
     */
    public $author = '';
    
    /**
     * @var string URL to plugin's website
     */
    public $url = '';
    
    /**
     * @var string Current version of plugin
     */
    public $version = '';
    
    /**
     * Plugin gateway into codebase
     */
    abstract public function load();
    
    /**
     * Outputs the settings page HTML and handles form posts on the plugin's
     * settings page.
     */
    public function settings(){}
    
    /**
     * Performs install operations for plugin. Called when user clicks install
     * plugin in admin panel.
     */
    public function install(){}
    
    /**
     * Performs uninstall operations for plugin. Called when user clicks
     * uninstall plugin in admin panel and prior to files being removed.
     */
    public function uninstall(){}
    
    
    final public function getSystemName()
    {
        return get_class($this);
    }
}