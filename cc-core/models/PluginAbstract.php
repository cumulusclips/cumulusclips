<?php

abstract class PluginAbstract
{
    /**
     * Attaches plugin to plugin's HTML Head 
     */
    abstract public static function load();
 
    /**
     * Provides information regarding plugin to Admin Panel
     * @return array Plugin information
     */
    abstract public static function info();

    /**
     * Display and process the settings form for the plugin 
     */
    public static function settings(){}
    
    /**
     * Perform install procedure required by plugin 
     */
    public static function install(){}
    
    /**
     * Remove settings stored in db by plugin 
     */
    public static function uninstall(){}
}