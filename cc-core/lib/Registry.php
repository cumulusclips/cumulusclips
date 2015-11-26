<?php

/**
 * Provides an interface for accessing shared objects without the use of global
 */
class Registry
{
    /**
     * @var array List containing all registry entries for an instance
     */
    protected static $_registryEntries = array();
    
    /**
     * Store an entry into the registry
     * @param string $key Name of entry to be stored
     * @param mixed $value Value of entry
     */
    public static function set($key, $value)
    {
        self::$_registryEntries[$key] = $value;
    }
    
    /**
     * Retrieve a value stored in the registry
     * @param string $key Name of entry to be retrieved
     * @return mixed Value of stored entry
     * @throws Exception If entry with given key doesn't exist
     */
    public static function get($key)
    {
        if (!isset(self::$_registryEntries[$key])) {
            throw new Exception('Unknown entry for key ' . $key . ' in registry');
        }
        return self::$_registryEntries[$key];
    }
    
    /**
     * Check whether an entry exists of not
     * @param string $key Name of entry to verify
     * @return boolean Returns true if entry exists false otherwise 
     */
    public static function isRegistered($key)
    {
        return array_key_exists($key, self::$_registryEntries);
    }
    
    /**
     * Retrieve entire registry as an array
     * @return array Returns all registry entries 
     */
    public static function getRegistry()
    {
        return self::$_registryEntries;
    }
}