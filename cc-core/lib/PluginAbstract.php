<?php

abstract class PluginAbstract
{
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
     * The plugin's gateway into codebase. Place plugin hook attachments here.
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
    
    /**
     * Retrieves plugin's system name
     * @return string Returns plugin's system name
     */
    final public function getSystemName()
    {
        return get_class($this);
    }
}