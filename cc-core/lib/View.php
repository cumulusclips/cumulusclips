<?php

class View {
    
    // Object Properties
    public static $options;
    public static $vars;




    /**
     * Initialize view and set template & layout properties
     * @global object $db Instance of database object
     * @global object $config Site configuration settings
     * @param string $page [optional] Page whose information to load
     * @return void View is initialized
     */
    static function  InitView ($page = null) {
        global $db, $config;
        self::$options = new stdClass();
        self::$options->layout = 'default';
        self::$options->header = THEME_PATH . '/layouts/' . self::$options->layout . '.header.tpl';
        self::$options->footer = THEME_PATH . '/layouts/' . self::$options->layout . '.footer.tpl';
        self::$options->blocks = array();

        self::$vars = new stdClass();
        self::$vars->db = $db;
        self::$vars->config = $config;

        // Load page's meta information into memory for use in templates
        if ($page) {
            self::$options->page = $page;
            self::$vars->meta = Language::GetMeta ($page);
            if (empty (self::$vars->meta->title)) self::$vars->meta->title = $config->sitename;
        }

        Plugin::Trigger ('view.init');

    }
    



    /**
     * Output the requested page to the browser
     * @param string $view The view file to be output
     * @return mixed Page is output to browser
     */
    static function Render ($view) {
        self::$options->view = THEME_PATH . '/' . $view;
        extract (get_object_vars (self::$vars));
        Plugin::Trigger ('view.render');
        include (self::$options->view);
    }




    /**
     * Switch the layout to be used
     * @param string $layout The new layout to switch to
     * @return void Layout is updated and new templates are used
     */
    static function SetLayout ($layout) {
        self::$options->layout = $layout;
        $header = THEME_PATH . "/layouts/$layout.header.tpl";
        $footer = THEME_PATH . "/layouts/$layout.footer.tpl";
        if (file_exists ($header)) self::$options->header = $header;
        if (file_exists ($header)) self::$options->footer = $footer;
        Plugin::Trigger ('view.set_layout');
    }




    /**
     * Output the layout header to the browser
     * @return mixed Header is output to browser
     */
    static function Header() {
        extract (get_object_vars (self::$vars));
        Plugin::Trigger ('view.header');
        include (self::$options->header);
    }




    /**
     * Output the layout footer to the browser
     * @return mixed Footer is output to browser
     */
    static function Footer() {
        extract (get_object_vars (self::$vars));
        Plugin::Trigger ('view.footer');
        include (self::$options->footer);
    }




    /**
     * Output a custom block to the browser
     * @param string $tpl_file Name of the block to be output
     * @return mixed Block is output to browser
     */
    static function Block ($tpl_file) {
        extract (get_object_vars (self::$vars));
        $block = (file_exists (THEME_PATH . '/blocks/' . $tpl_file)) ? THEME_PATH . '/blocks/' . $tpl_file : $tpl_file;
        Plugin::Trigger ('view.block');
        include ($block);
    }




    /**
     * Repeat output of a block based on list of records
     * @param string $tpl_file Name of the block to be repeated
     * @param array $records List of records to loop through
     * @return mixed The given block is output according to the number entries in the list
     */
    static function RepeatingBlock ($tpl_file, $records) {

        extract (get_object_vars (self::$vars));
        $block = (file_exists (THEME_PATH . '/blocks/' . $tpl_file)) ? THEME_PATH . '/blocks/' . $tpl_file : $tpl_file;
        Plugin::Trigger ('view.repeating_block');

        foreach ($records as $_id) {
            Plugin::Trigger ('view.repeating_block_loop');
            include ($block);
        }

    }




    /**
     * Add specified block to sidebar queue for later output
     * @param string $tpl_file The block to be loaded into the sidebar queue
     * @return void Block is queued for later output
     */
    static function AddSidebarBlock ($tpl_file) {
        self::$options->blocks[] = $tpl_file;
        Plugin::Trigger ('view.add_sidebar_block');
    }




    /**
     * Write queued sidebar blocks to the browser
     * @return mixed Sidebar blocks are output
     */
    static function WriteSidebarBlocks() {
        Plugin::Trigger ('view.write_sidebar_blocks');
        foreach (self::$options->blocks as $_block) {
            Plugin::Trigger ('view.write_sidebar_blocks_loop');
            self::Block($_block);
        }
    }




    /**
     * Retrieve page name, language, and layout type as class names
     * for use in theme as CSS Hooks
     * @return string Returns string of page name and layout type
     */
    static function CssHooks() {
        return self::$options->page . ' ' . self::$options->layout . ' ' . Language::GetCSSName();
    }




    /**
     * Add CSS file to the document
     * @param string $css_name Filename of the CSS file to be attached
     * @return void CSS file is stored to be written in document
     */
    static function AddCss ($css_name) {
        $css_url = (file_exists (THEME_PATH . '/css/' . $css_name)) ? THEME . '/css/' . $css_name : $css_name;
        self::$options->css[] = '<link rel="stylesheet" href="' . $css_url . '" />';
        Plugin::Trigger ('view.add_css');
    }




    /**
     * Write the additional CSS tags to the browser
     * @return mixed CSS link tags are written
     */
    static function WriteCss() {
        Plugin::Trigger ('view.write_css');
        if (isset (self::$options->css)) {
            foreach (self::$options->css as $_value) {
                Plugin::Trigger ('view.write_css_loop');
                echo $_value, "\n";
            }
        }
    }




    /**
     * Add JS file to the document
     * @param string $js_name Filename of the JS file to be attached
     * @return void JS file is stored to be written in document
     */
    static function AddJs ($js_name) {
        $js_url = (file_exists (THEME_PATH . '/js/' . $js_name)) ? THEME . '/js/' . $js_name : $js_name;
        self::$options->js[] = '<script type="text/javascript" src="' . $js_url . '"></script>';
        Plugin::Trigger ('view.add_js');
    }




    /**
     * Write the Additional JS tags to the browser
     * @return mixed JS src tags are written
     */
    static function WriteJs() {

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

        Plugin::Trigger ('view.write_js');
        if (isset (self::$options->js)) {
            foreach (self::$options->js as $_value) {
                Plugin::Trigger ('view.write_js_loop');
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
    static function AddMeta ($meta_name, $meta_content) {
        self::$options->meta[] = '<meta name="' . $meta_name . '" content="' . $meta_content . '" />';
        Plugin::Trigger ('view.add_meta');
    }




    /**
     * Write the Additional META tags to the browser
     * @return mixed Stored META tags are written to browser
     */
    static function WriteMeta() {
        
        Plugin::Trigger ('view.write_meta');
        self::AddMeta ('generator', 'CumulusClips');
        if (!empty (self::$vars->meta->keywords)) self::AddMeta ('keywords', self::$vars->meta->keywords);
        if (!empty (self::$vars->meta->description)) self::AddMeta ('description', self::$vars->meta->description);
        
        if (isset (self::$options->meta)) {
            foreach (self::$options->meta as $_value) {
                Plugin::Trigger ('view.write_meta_loop');
                echo $_value, "\n";
            }
        }
    }

}

?>