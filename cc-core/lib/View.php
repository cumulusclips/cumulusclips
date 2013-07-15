<?php

class View
{
    // Object Properties
    public static $options;
    public static $vars;

    /**
     * Initialize view and set template & layout properties
     * @param string $page [optional] Page whose information to load
     * @return void View is initialized
     */
    public static function initView($page = null)
    {
        self::$vars = new stdClass();
        self::$vars->db = Registry::get('db');
        self::$vars->config = Registry::get('config');

        self::$options = new stdClass();
        self::$options->layout = 'default';
        self::$options->header = self::GetFallbackPath('layouts/' . self::$options->layout . '.header.tpl');
        self::$options->footer = self::GetFallbackPath('layouts/' . self::$options->layout . '.footer.tpl');
        self::$options->blocks = array();

        // Load page's meta information into memory for use in templates
        if ($page) {
            self::$options->page = $page;
            self::$vars->meta = Language::GetMeta($page);
            if (empty(self::$vars->meta->title)) self::$vars->meta->title = self::$vars->config->sitename;
        }

        Plugin::triggerEvent('view.init');
    }
    
    /**
     * Determine which theme to obtain requested file path from.
     * @param string $file Path of file to check relative to theme root
     * @return mixed If file is found in current theme, it's path is returned
     * If file is not found in current theme, but rather default theme, it's path is returned.
     * Returns boolean false if file is not found in either theme.
     */
    public static function getFallbackPath($file)
    {
        $config = Registry::get('config');
        if (file_exists($config->theme_path . "/$file")) {
            return $config->theme_path . "/$file";
        } else if (file_exists($config->theme_path_default . "/$file")) {
            return $config->theme_path_default . "/$file";
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
    public static function getFallbackUrl($file)
    {
        $config = Registry::get('config');
        if (file_exists($config->theme_path . "/$file")) {
            return $config->theme_url . "/$file";
        } else if (file_exists($config->theme_path_default . "/$file")) {
            return $config->theme_url_default . "/$file";
        } else {
            return false;
        }
    }
    
    /**
     * Output the requested page to the browser
     * @param string $view The view file to be output
     * @return mixed Page is output to browser
     */
    public static function render($view)
    {
        self::$options->view = self::GetFallbackPath($view);
        extract(get_object_vars(self::$vars));
        Plugin::Trigger('view.render');
        include(self::$options->view);
    }
    
    /**
     * Switch the layout to be used
     * @param string $layout The new layout to switch to
     * @return void Layout is updated and new templates are used
     */
    public static function setLayout($layout)
    {
        self::$options->layout = $layout;
        $header = self::GetFallbackPath("layouts/$layout.header.tpl");
        $footer = self::GetFallbackPath("layouts/$layout.footer.tpl");
        if (file_exists($header)) self::$options->header = $header;
        if (file_exists($header)) self::$options->footer = $footer;
        Plugin::Trigger('view.set_layout');
    }
    
    /**
     * Output the layout header to the browser
     * @return mixed Header is output to browser
     */
    public static function header()
    {
        extract(get_object_vars(self::$vars));
        Plugin::Trigger('view.header');
        include(self::$options->header);
    }
    
    /**
     * Output the layout footer to the browser
     * @return mixed Footer is output to browser
     */
    public static function footer()
    {
        extract(get_object_vars(self::$vars));
        Plugin::Trigger('view.footer');
        include(self::$options->footer);
    }
    
    /**
     * Output a custom block to the browser
     * @param string $tpl_file Name of the block to be output
     * @return mixed Block is output to browser
     */
    public static function block($tpl_file)
    {
        // Detect correct block path
        $request_block = self::GetFallbackPath("blocks/$tpl_file");
        $block = ($request_block) ? $request_block : $tpl_file;

        extract(get_object_vars(self::$vars));
        Plugin::Trigger('view.block');
        include($block);
    }
    
    /**
     * Repeat output of a block based on list of records
     * @param string $tpl_file Name of the block to be repeated
     * @param array $records List of records to loop through
     * @return mixed The given block is output according to the number entries in the list
     */
    public static function repeatingBlock($tpl_file, $records)
    {
        // Detect correct block path
        $request_block = self::GetFallbackPath("blocks/$tpl_file");
        $block = ($request_block) ? $request_block : $tpl_file;
        
        extract(get_object_vars(self::$vars));
        Plugin::Trigger('view.repeating_block');

        foreach ($records as $_id) {
            Plugin::Trigger('view.repeating_block_loop');
            include($block);
        }
    }
    
    /**
     * Add specified block to sidebar queue for later output
     * @param string $tpl_file The block to be loaded into the sidebar queue
     * @return void Block is queued for later output
     */
    public static function addSidebarBlock($tpl_file)
    {
        self::$options->blocks[] = $tpl_file;
        Plugin::Trigger('view.add_sidebar_block');
    }
    
    /**
     * Write queued sidebar blocks to the browser
     * @return mixed Sidebar blocks are output
     */
    public static function writeSidebarBlocks()
    {
        Plugin::Trigger('view.write_sidebar_blocks');
        foreach (self::$options->blocks as $_block) {
            Plugin::Trigger('view.write_sidebar_blocks_loop');
            self::Block($_block);
        }
    }
    
    /**
     * Retrieve page name, language, and layout type as class names
     * for use in theme as CSS Hooks
     * @return string Returns string of page name and layout type
     */
    public static function cssHooks()
    {
        return self::$options->page . ' ' . self::$options->layout . ' ' . Language::GetCSSName();
    }

    /**
     * Add CSS file to the document
     * @param string $css_name Filename of the CSS file to be attached
     * @return void CSS file is stored to be written in document
     */
    public static function addCss($css_name)
    {
        // Detect correct file path
        $request_file = self::GetFallbackUrl("css/$css_name");
        $css_url = ($request_file) ? $request_file : $css_name;

        self::$options->css[] = '<link rel="stylesheet" href="' . $css_url . '" />';
        Plugin::Trigger('view.add_css');
    }

    /**
     * Write the additional CSS tags to the browser
     * @return mixed CSS link tags are written
     */
    public static function writeCss()
    {
        Plugin::Trigger('view.write_css');
        if (isset(self::$options->css)) {
            foreach (self::$options->css as $_value) {
                Plugin::Trigger('view.write_css_loop');
                echo $_value, "\n";
            }
        }
    }

    /**
     * Add JS file to the document
     * @param string $js_name Filename of the JS file to be attached
     * @return void JS file is stored to be written in document
     */
    public static function addJs($js_name)
    {
        // Detect correct file path
        $request_file = self::GetFallbackUrl("js/$js_name");
        $js_url = ($request_file) ? $request_file : $js_name;

        self::$options->js[] = '<script type="text/javascript" src="' . $js_url . '"></script>';
        Plugin::Trigger('view.add_js');
    }

    /**
     * Write the Additional JS tags to the browser
     * @return mixed JS src tags are written
     */
    public static function writeJs()
    {
        // Add theme preview JS
	if (PREVIEW_THEME) {
            $js_theme_preview = '<script type="text/javascript">';
            $js_theme_preview .= "for (var i = 0; i < document.links.length; i++) document.links[i].href = document.links[i].href + '?preview_theme=" . PREVIEW_THEME . "';";
            $js_theme_preview .= '</script>';
            self::$options->js[] = $js_theme_preview;
        }

        // Add language preview JS
	if (PREVIEW_LANG) {
            $js_lang_preview = '<script type="text/javascript">';
            $js_lang_preview .= "for (var i = 0; i < document.links.length; i++) document.links[i].href = document.links[i].href + '?preview_lang=" . PREVIEW_LANG . "';";
            $js_lang_preview .= '</script>';
            self::$options->js[] = $js_lang_preview;
        }

        Plugin::Trigger('view.write_js');
        if (isset(self::$options->js)) {
            foreach (self::$options->js as $_value) {
                Plugin::Trigger('view.write_js_loop');
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
    public static function addMeta($meta_name, $meta_content)
    {
        self::$options->meta[] = '<meta name="' . $meta_name . '" content="' . $meta_content . '" />';
        Plugin::Trigger('view.add_meta');
    }

    /**
     * Write the Additional META tags to the browser
     * @return mixed Stored META tags are written to browser
     */
    public static function writeMeta()
    {
        Plugin::Trigger('view.write_meta');
        self::AddMeta('generator', 'CumulusClips');
        if (!empty(self::$vars->meta->keywords)) self::AddMeta('keywords', self::$vars->meta->keywords);
        if (!empty(self::$vars->meta->description)) self::AddMeta('description', self::$vars->meta->description);
        
        if (isset(self::$options->meta)) {
            foreach (self::$options->meta as $_value) {
                Plugin::Trigger('view.write_meta_loop');
                echo $_value, "\n";
            }
        }
    }
}