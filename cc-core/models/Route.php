<?php

class Route
{
    /**
     * @var string REGEX pattern to be matched against the request URI
     * Omit leading & trailing slashes, as well as REGEX beginning and ending
     * characters, these are accounted for later on.
     * @example videos/([0-9]+)/([a-z0-9\-]+)
     */
    public $path = null;
    
    /**
     * @var string File path to controller to be loaded, relative to DOC_ROOT/
     * @example cc-core/controllers/index.php
     */
    public $location = null;
    
    /**
     * @var array List of key names to be injected into $_GET
     * and populated with data from URI pattern matches. Values are mapped to
     * the corresponding REGEX pattern match found in self::$path.
     * @example $mappings[0] => 'page' will create $_GET['page']
     */
    public $mappings = null;
    
    /**
     * Create a new URL route for loading a given controller based on the request URI
     * @param array $options (optional)
     * @throws Exception If mappings option is invalid
     */
    public function __construct($options = array())
    {
        if (!empty($options)) {
            
            if (!empty($options['path'])) {
                $this->path = $options['path'];
            }
            
            if (!empty($options['location'])) {
                $this->location = $options['location'];
            }

            if (!empty($options['mappings'])) {
                if (!is_array($options['mappings'])) throw new Exception('Invalid mappings property in route');
                $this->mappings = $options['mappings'];
            }
        }
    }
}