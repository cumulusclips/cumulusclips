<?php

class Route
{
    const STANDARD = 1;
    const MOBILE = 2;
    const AGNOSTIC = 3;
    
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
     * @var array List of key names that map to regex groups in Route::$path.
     * These keys are used as the indexes in the $_GET super global
     * with the value being the corresponding regex group from the URI.
     * @example Route::$mappings = array('page') will create $_GET['page']
     */
    public $mappings = null;
    
    /**
     * @var string Page name given for route. When provided this becomes the CSS
     * selector name for the given page 
     */
    public $name = null;

    /**
     * @var string If route is merely a URI variant of another route, this value lists the original route
     */
    public $canonical = null;
    
    /**
     * @var int Intended audience for the route. Defines whether it's Mobile vs Desktop vs Agnostic
     */
    public $type = null;
    
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
            
            if (isset($options['mobile']) && is_bool($options['mobile'])) {
                $this->mobile = $options['mobile'];
            }
            
            if (isset($options['type']) && in_array($options['type'], array(self::STANDARD, self::MOBILE, self::AGNOSTIC))) {
                $this->type = $options['type'];
            } else {
                $this->type = self::STANDARD;
            }
        }
    }
    
    /**
     * Evaluates whether current route is for mobile platforms or otherwise
     * @return boolean Returns true if route is for mobile, false otherwise
     */
    public function isMobile()
    {
        return $this->type == self::MOBILE;
    }
}