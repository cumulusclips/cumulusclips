<?php

class View
{
    // Object Properties
    public $options;
    public $vars;
    protected $_body;
    protected static $_view;
    public $disableView = false;
    protected $_route;
    
    public function __construct()
    {
        $this->vars = new stdClass();
        $this->vars->db = Registry::get('db');
        $this->vars->config = Registry::get('config');

        $this->options = new stdClass();
        $this->options->themeFile = null;
        $this->options->layout = 'default';
        $this->options->blocks = array();
        $this->options->css = array();
        $this->options->js = array();
        $this->_route = Registry::get('route');
        
        // Define theme configuration
        try {
            $isMobile = Registry::get('route')->mobile;
        } catch (Exception $e) {
            $isMobile = false;
        }
        $theme = $this->_currentTheme($isMobile);
        $themeFiltered = Plugin::triggerFilter('app.before_set_theme', $theme);
        define('THEME', HOST . '/cc-content/themes/' . $themeFiltered);
        define('THEME_PATH', THEMES_DIR . '/' . $themeFiltered);
        $this->vars->config->theme = $themeFiltered;
        $this->vars->config->theme_url = THEME;
        $this->vars->config->theme_path = THEME_PATH;
        
        // Retrieve page
        $this->options->page = $this->_getPageFromRoute($this->_route);

        // Retrieve meta data
        $this->vars->meta = Language::GetMeta($this->options->page);
        if (empty($this->vars->meta->title)) $this->vars->meta->title = $this->vars->config->sitename;
        
        // Load view helper
        $viewHelper = $this->getFallbackPath('helper.php');
        if ($viewHelper && file_exists($viewHelper)) include($viewHelper);
        self::$_view = $this;
    }
    
    /**
     * Determine which theme to obtain requested file path from.
     * @param string $file Path of file to check relative to theme root
     * @return mixed If file is found in current theme, it's path is returned
     * If file is not found in current theme, but rather default theme, it's path is returned.
     * Returns boolean false if file is not found in either theme.
     */
    public function getFallbackPath($file)
    {
        if (file_exists($this->vars->config->theme_path . "/$file")) {
            return $this->vars->config->theme_path . "/$file";
        } else if (file_exists($this->vars->config->theme_path_default . "/$file")) {
            return $this->vars->config->theme_path_default . "/$file";
        } else {
            return false;
        }
    }
    
    /**
     * Determine which theme to obtain requested file URL from.
     * @param string $file Path of file to check relative to theme root
     * @return mixed If file is found in current theme, it's URL is returned
     * If file is not found in current theme, but rather default theme, it's URL is returned.
     * Returns boolean false if file is not found in either theme.
     */
    public function getFallbackUrl($file)
    {
        if (file_exists($this->vars->config->theme_path . "/$file")) {
            return $this->vars->config->theme_url . "/$file";
        } else if (file_exists($this->vars->config->theme_path_default . "/$file")) {
            return $this->vars->config->theme_url_default . "/$file";
        } else {
            return false;
        }
    }
    
    /**
     * Retrieve or generate a page name from a route
     * @param Route $route Route to get page name for
     * @return string Returns Route's page name in route if it exists, generated based on path otherwise 
     */
    protected function _getPageFromRoute(Route $route)
    {
        $patterns = array(
            '/cc\-core\/controllers\//i',
            '/\//',
            '/\.php$/i'
        );
        $replacements = array(
            '',
            '_',
            ''
        );
        return (!empty($route->name)) ? $route->name : preg_replace($patterns, $replacements, $route->location);
    }
    
    /**
     * Generate a theme file from a route's location path
     * @param Route $route Route to extract location from
     * @return string Theme file is returned
     */ 
    protected function _getThemeFileFromRoute(Route $route)
    {
        $patterns = array(
            '/cc\-core\/controllers\//i',
            '/\.php$/i'
        );
        $replacements = array(
            '',
            '.tpl'
        );
        return preg_replace($patterns, $replacements, $route->location);
    }
    
    /**
     * Output to the browser the view corresponding to the requested script
     * @return mixed View is output to browser
     */
    public function render()
    {
        if (!$this->disableView) {
            // Retrieve theme file
            if (empty($this->options->themeFile)) {
                $themeFileName = $this->_getThemeFileFromRoute($this->_route);
                $this->options->themeFile = $this->getFallbackPath($themeFileName);
                if ($this->options->themeFile === false) throw new Exception('Missing theme file');
            }
            extract(get_object_vars($this->vars));
            
            // Catch output of body
            ob_start();
            include($this->options->themeFile);
            $this->_body = ob_get_contents();
            ob_end_clean();

            // Output layout of page
            include($this->getFallbackPath('layouts/' . $this->options->layout . '.phtml'));
        }
    }
    
    /**
     * Retrieve the HTML for the body of a page
     * @return string Returns the HTML for the body of a page
     */
    public function body()
    {
        return Plugin::triggerFilter('view.body', $this->_body);
    }
    
    /**
     * Switch the layout to be used
     * @param string $layout The new layout to switch to
     * @return void Layout is updated and new templates are used
     */
    public function setLayout($layout)
    {
        $layoutPath = $this->getFallbackPath("layouts/$layout.phtml");
        if (file_exists($layoutPath)) {
            $this->options->layout = $layout;
        } else {
            throw new Exception('Unknown layout "' . $layout . '"');
        }
        Plugin::triggerEvent('view.set_layout');
    }
    
    /**
     * Output a custom block to the browser
     * @param string $tpl_file Name of the block to be output
     * @return mixed Block is output to browser
     */
    public function block($tpl_file)
    {
        // Detect correct block path
        $request_block = $this->getFallbackPath("blocks/$tpl_file");
        $block = ($request_block) ? $request_block : $tpl_file;

        extract(get_object_vars($this->vars));
        Plugin::triggerEvent('view.block');
        include($block);
    }
    
    /**
     * Repeat output of a block based on list of records
     * @param string $tpl_file Name of the block to be repeated
     * @param array $records List of records to loop through
     * @return mixed The given block is output according to the number entries in the list
     */
    public function repeatingBlock($tpl_file, $records)
    {
        // Detect correct block path
        $request_block = $this->getFallbackPath("blocks/$tpl_file");
        $block = ($request_block) ? $request_block : $tpl_file;
        
        extract(get_object_vars($this->vars));
        Plugin::triggerEvent('view.repeating_block');

        foreach ($records as $model) {
            Plugin::triggerEvent('view.repeating_block_loop');
            include($block);
        }
    }
    
    /**
     * Add specified block to sidebar queue for later output
     * @param string $tpl_file The block to be loaded into the sidebar queue
     * @return void Block is queued for later output
     */
    public function addSidebarBlock($tpl_file)
    {
        $this->options->blocks[] = $tpl_file;
        Plugin::triggerEvent('view.add_sidebar_block');
    }
    
    /**
     * Write queued sidebar blocks to the browser
     * @return mixed Sidebar blocks are output
     */
    public function writeSidebarBlocks()
    {
        Plugin::triggerEvent('view.write_sidebar_blocks');
        foreach ($this->options->blocks as $_block) {
            Plugin::triggerEvent('view.write_sidebar_blocks_loop');
            $this->block($_block);
        }
    }
    
    /**
     * Retrieve page name, language, and layout type as class names
     * for use in theme as CSS Hooks
     * @return string Returns string of page name and layout type
     */
    public function cssHooks()
    {
        return $this->options->page . ' ' . $this->options->layout . ' ' . Language::GetCSSName();
    }

    /**
     * Add CSS file to the document
     * @param string $css_name Filename of the CSS file to be attached
     * @return void CSS file is stored to be written in document
     */
    public function addCss($css_name)
    {
        // Detect correct file path
        $request_file = $this->getFallbackUrl("css/$css_name");
        $css_url = ($request_file) ? $request_file : $css_name;

        $this->options->css[] = '<link rel="stylesheet" href="' . $css_url . '" />';
        Plugin::triggerEvent('view.add_css');
    }

    /**
     * Write the additional CSS tags to the browser
     * @return mixed CSS link tags are written
     */
    public function writeCss()
    {
        Plugin::triggerEvent('view.write_css');
        if (isset($this->options->css)) {
            foreach ($this->options->css as $_value) {
                Plugin::triggerEvent('view.write_css_loop');
                echo $_value, "\n";
            }
        }
    }

    /**
     * Add JS file to the document
     * @param string $js_name Filename of the JS file to be attached
     * @return void JS file is stored to be written in document
     */
    public function addJs($js_name)
    {
        // Detect correct file path
        $request_file = $this->getFallbackUrl("js/$js_name");
        $js_url = ($request_file) ? $request_file : $js_name;

        $this->options->js[] = '<script type="text/javascript" src="' . $js_url . '"></script>';
        Plugin::triggerEvent('view.add_js');
    }

    /**
     * Write the Additional JS tags to the browser
     * @return mixed JS src tags are written
     */
    public function writeJs()
    {
        // Add theme preview JS
        if (PREVIEW_THEME) {
            $js_theme_preview = '<script type="text/javascript">';
            $js_theme_preview .= "for (var i = 0; i < document.links.length; i++) document.links[i].href = document.links[i].href + '?preview_theme=" . PREVIEW_THEME . "';";
            $js_theme_preview .= '</script>';
            $this->options->js[] = $js_theme_preview;
        }

        // Add language preview JS
        if (PREVIEW_LANG) {
            $js_lang_preview = '<script type="text/javascript">';
            $js_lang_preview .= "for (var i = 0; i < document.links.length; i++) document.links[i].href = document.links[i].href + '?preview_lang=" . PREVIEW_LANG . "';";
            $js_lang_preview .= '</script>';
            $this->options->js[] = $js_lang_preview;
        }

        Plugin::triggerEvent('view.write_js');
        if (isset($this->options->js)) {
            foreach ($this->options->js as $_value) {
                Plugin::triggerEvent('view.write_js_loop');
                echo $_value, "\n";
            }
        }
    }

    /**
     * Add META tag to the document head
     * @param string $meta_name Name attribute to be assigned to the META tag
     * @param string $meta_content Content for the META tag
     * @return void META tag is stored to be written in document head
     */
    public function addMeta($meta_name, $meta_content)
    {
        $this->options->meta[] = '<meta name="' . $meta_name . '" content="' . $meta_content . '" />';
        Plugin::triggerEvent('view.add_meta');
    }

    /**
     * Write the Additional META tags to the browser
     * @return mixed Stored META tags are written to browser
     */
    public function writeMeta()
    {
        Plugin::triggerEvent('view.write_meta');
        $this->addMeta('generator', 'CumulusClips');
        if (!empty($this->vars->meta->keywords)) $this->addMeta('keywords', $this->vars->meta->keywords);
        if (!empty($this->vars->meta->description)) $this->addMeta('description', $this->vars->meta->description);
        
        if (isset($this->options->meta)) {
            foreach ($this->options->meta as $_value) {
                Plugin::triggerEvent('view.write_meta_loop');
                echo $_value, "\n";
            }
        }
    }
    
    /**
     * Determine which theme should be used
     * @param boolean $isMobile Whether or not the platform being loaded is mobile
     * @return string Theme to be used
     */
    protected function _currentTheme($isMobile = false)
    {
        // Determine active theme
        $active_theme = ($isMobile) ? Settings::get('active_mobile_theme') : Settings::get('active_theme');

        // Check if 'Preview' theme was provided
        $preview_theme = false;
        if (isset ($_GET['preview_theme']) && Functions::ValidTheme ($_GET['preview_theme'])) {
            $active_theme = $_GET['preview_theme'];
            $preview_theme = $_GET['preview_theme'];
        }

        define ('PREVIEW_THEME', $preview_theme);
        return $active_theme;
    }
    
    /**
     * Retrieve an instance of a view
     * @return View Returns existing view or new one if none has been created
     */
    public static function getView()
    {
        return (self::$_view instanceof View) ? self::$_view : new View();
    }

    /**
     * Retrieve instance of service of a given domain
     * @param string $service Name of domain service to instanciate
     * @return ServiceAbstract Returns instance of given service class
     */
    public static function getService($service)
    {
        $class = $service . 'Service';
        return new $class;
    }

    /**
     * Retrieve instance of model mapper for a given domain
     * @param string $mapper Name of domain model mapper to instanciate
     * @return MapperAbstract Returns instance of given domain mapper class
     */
    public static function getMapper($mapper)
    {
        $class = $mapper . 'Mapper';
        return new $class;
    }
}