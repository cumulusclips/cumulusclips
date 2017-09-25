<?php

class View
{
    // Object Properties
    public $options;
    public $vars;
    protected $_body;
    protected $_route;
    protected $_js;
    protected $_css;
    protected $_blocks;
    protected $_bodyClasses = array();

    /**
     * Creates a new view
     */
    public function __construct()
    {
        $this->vars = new stdClass();
        $this->vars->db = Registry::get('db');
        $this->vars->config = Registry::get('config');

        $this->options = new stdClass();
        $this->options->disableView = false;
        $this->options->disableLayout = false;
        $this->options->theme = null;
        $this->options->themeUrl = null;
        $this->options->themePath = null;
        $this->options->viewFile = null;
        $this->options->layout = 'default';
        $this->_blocks = array();
        $this->_css = array();
        $this->_js = array();
    }

    /**
     * Loads theme, meta language, and prepares view to render a given route
     * @param Route $route Route to be rendered
     * @return View Provides fluid interface
     */
    public function load(Route $route)
    {
        $this->_route = $route;

        // Define theme configuration
        $this->options->theme = $this->_currentTheme($this->_route->isMobile());
        $this->options->themeUrl = HOST . '/cc-content/themes/' . $this->options->theme;
        $this->options->themePath = THEMES_DIR . '/' . $this->options->theme;

        // Set default theme settings
        $this->options->defaultTheme = ($this->_route->isMobile()) ? 'mobile-default' : 'default';
        $this->options->defaultThemeUrl = HOST . '/cc-content/themes/' . $this->options->defaultTheme;
        $this->options->defaultThemePath = THEMES_DIR . '/' . $this->options->defaultTheme;

        // Load theme specific language entries
        Language::loadThemeLanguage($this->options->theme);

        // Load theme's plugin
        Plugin::loadThemePlugin($this->options->theme);

        // Load view helper
        $viewHelper = $this->getFallbackPath('helper.php');
        if ($viewHelper && file_exists($viewHelper)) include_once($viewHelper);

        // Retrieve page
        $this->options->page = $this->_getPageFromRoute($this->_route);

        // Retrieve meta data
        $this->vars->meta = Language::getMeta($this->options->page);
        if (empty($this->vars->meta->title)) $this->vars->meta->title = $this->vars->config->sitename;

        return $this;
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
        if (file_exists($this->options->themePath . "/$file")) {
            return $this->options->themePath . "/$file";
        } else if (file_exists($this->options->defaultThemePath . "/$file")) {
            return $this->options->defaultThemePath . "/$file";
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
        if (file_exists($this->options->themePath . "/$file")) {
            return $this->options->themeUrl . "/$file";
        } else if (file_exists($this->options->defaultThemeUrl . "/$file")) {
            return $this->options->defaultThemeUrl . "/$file";
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
            '/' . str_replace('/', '\/', preg_quote(DOC_ROOT . '/cc-core/controllers/')) . '?/i',
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
    protected function _getViewFileFromRoute(Route $route)
    {
        $patterns = array(
            '/' . str_replace('/', '\/', preg_quote(DOC_ROOT . '/cc-core/controllers') . '/(mobile/)') . '?/i',
            '/\.php$/i'
        );
        $replacements = array(
            '',
            '.phtml'
        );
        return preg_replace($patterns, $replacements, $route->location);
    }

    /**
     * Output to the browser the view corresponding to the requested script
     * @return mixed View is output to browser
     */
    public function render()
    {
        if (!$this->options->disableView) {
            // Retrieve theme file
            if (empty($this->options->viewFile)) {
                $viewFileName = $this->_getViewFileFromRoute($this->_route);
                $this->options->viewFile = $this->getFallbackPath($viewFileName);
                if ($this->options->viewFile === false) throw new Exception('Missing theme file');
            }
            extract(get_object_vars($this->vars));

            // Catch output of body
            ob_start();
            include($this->options->viewFile);
            $this->_body = Plugin::triggerFilter('view.render_body', ob_get_contents());
            ob_end_clean();

            // Output page
            if ($this->options->disableLayout) {
                echo $this->body();
            } else {
                include($this->getFallbackPath('layouts/' . $this->options->layout . '.phtml'));
            }
        }
    }

    /**
     * Retrieve the HTML for the body of a page
     * @return string Returns the HTML for the body of a page
     */
    public function body()
    {
        return $this->_body;
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
    }

    /**
     * Output a custom block to the browser
     * @param string $viewFile Name of the block to be output
     * @return mixed Block is output to browser
     */
    public function block($viewFile)
    {
        // Detect correct block path
        $request_block = $this->getFallbackPath("blocks/$viewFile");
        $block = ($request_block) ? $request_block : $viewFile;

        extract(get_object_vars($this->vars));
        include($block);
    }

    /**
     * Repeat output of a block based on list of records
     * @param string $viewFile Name of the block to be repeated
     * @param array $records List of records to loop through
     * @return mixed The given block is output according to the number entries in the list
     */
    public function repeatingBlock($viewFile, $records)
    {
        // Detect correct block path
        $request_block = $this->getFallbackPath("blocks/$viewFile");
        $block = ($request_block) ? $request_block : $viewFile;

        extract(get_object_vars($this->vars));

        foreach ($records as $model) {
            include($block);
        }
    }

    /**
     * Add specified block to sidebar queue for later output
     * @param string $viewFile The block to be loaded into the sidebar queue
     * @return void Block is queued for later output
     */
    public function addSidebarBlock($viewFile)
    {
        $this->_blocks[] = $viewFile;
    }

    /**
     * Write queued sidebar blocks to the browser
     * @return mixed Sidebar blocks are output
     */
    public function writeSidebarBlocks()
    {
        foreach ($this->_blocks as $_block) {
            $this->block($_block);
        }
    }

    /**
     * Queues a CSS class to be appended to the body tag
     * @param string $class Name of class to be added to body tag
     */
    public function addBodyClass($class)
    {
        $this->_bodyClasses[] = $class;
    }

    /**
     * Retrieve CSS classes to be added to the body tag. These include:
     *  - Page Name
     *  - Currently Active Language
     *  - Layout
     *  - Any Manually Added CSS Classes
     * @return string Returns string of page name and layout type
     */
    public function cssHooks()
    {
        $additionalClasses = (!empty($this->_bodyClasses)) ? ' ' . implode(' ', $this->_bodyClasses) : '';
        return $this->options->page . ' ' . $this->options->layout . ' ' . Language::getCSSName() . $additionalClasses;
    }

    /**
     * Queues a CSS file to be attached to the document
     * @param string $css_name Filename of the CSS file to be attached
     * @return void CSS file is queued
     */
    public function addCss($css_name)
    {
        // Detect correct file path
        $request_file = $this->getFallbackUrl("css/$css_name");
        $css_url = ($request_file) ? $request_file : $css_name;
        $this->_css[] = $css_url;
    }

    /**
     * Writes queued CSS files to the DOM
     * @return mixed CSS link tags are written
     */
    public function writeCss()
    {
        // Output system generated CSS
        echo '<link rel="stylesheet" href="' . HOST . '/css/system.css" />' . "\n";

        // Output queued CSS files
        if (isset($this->_css)) {
            foreach ($this->_css as $_value) {
                echo '<link rel="stylesheet" href="' . $_value . '" />' . "\n";
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
        $this->_js[] = $js_url;
    }

    /**
     * Writes queued JS files to the DOM
     * @return void JS src tags are written
     */
    public function writeJs()
    {
        // Add theme preview JS
        if (isset($_GET['preview_theme'])) {
            $js_theme_preview = <<<JS
<script type="text/javascript">
    for (var i = 0; i < document.links.length; i++) {
        var anchor = document.createElement('a');
        anchor.href = document.links[i].href;

        // Append query string if link does not already have it
        if (anchor.search.indexOf('preview_theme={$_GET['preview_theme']}') === -1) {
            anchor.search += (anchor.search === '' ? '?' : '&') + 'preview_theme={$_GET['preview_theme']}';
            document.links[i].href = anchor.href;
        }
    }
</script>
JS;
            echo $js_theme_preview;
        }

        // Add language preview JS
        if (defined('PREVIEW_LANG')) {
            $previewLang = PREVIEW_LANG;
            $js_lang_preview = <<<JS
<script type="text/javascript">
    for (var i = 0; i < document.links.length; i++) {
        var anchor = document.createElement('a');
        anchor.href = document.links[i].href;

        // Append query string if link does not already have it
        if (anchor.search.indexOf('preview_lang={$previewLang}') === -1) {
            anchor.search += (anchor.search === '' ? '?' : '&') + 'preview_lang={$previewLang}';
            document.links[i].href = anchor.href;
        }
    }
</script>
JS;
            echo $js_lang_preview;
        }

        // Output system generated JS
        echo '<script type="text/javascript" src="' . HOST . '/js/system.js"></script>' . "\n";

        // Output queued JS files
        if (isset($this->_js)) {
            foreach ($this->_js as $_value) {
                echo '<script type="text/javascript" src="' . $_value . '"></script>' . "\n";
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
        $this->_meta[] = '<meta name="' . $meta_name . '" content="' . $meta_content . '" />';
    }

    /**
     * Write the Additional META tags to the browser
     * @return mixed Stored META tags are written to browser
     */
    public function writeMeta()
    {
        $this->addMeta('generator', 'CumulusClips');
        if (!empty($this->vars->meta->keywords)) $this->addMeta('keywords', htmlentities($this->vars->meta->keywords));
        if (!empty($this->vars->meta->description)) $this->addMeta('description', htmlentities($this->vars->meta->description));

        if (isset($this->_meta)) {
            foreach ($this->_meta as $_value) {
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
        if (isset($_GET['preview_theme']) && Functions::validTheme($_GET['preview_theme'])) {
            $active_theme = $_GET['preview_theme'];
        }

        return $active_theme;
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
