<?php

class View {
    
    // Object Properties
    private static $options;
    public static $vars;



    // Constructor Method
    static function  InitView() {
        global $db, $config;
        self::$options = new stdClass();
        self::$options->layout = THEME_PATH . '/layouts/two_column.layout.tpl';
        self::$options->header = THEME_PATH . '/blocks/header.tpl';
        self::$options->footer = THEME_PATH . '/blocks/footer.tpl';
        self::$options->blocks = array();

        self::$vars = new stdClass();
        self::$vars->db = $db;
        self::$vars->config = $config;

        Plugin::Trigger ('view.init');

    }



    /**
     * Load page's meta information into memory for use in templates
     * @param string $page The name of the page who's information to retrieve
     * @return void Meta information is loaded into the options var.
     */
    static function LoadPage ($page) {
        self::$options->page = $page;
        self::$vars->meta = Language::GetMeta ($page);
        Plugin::Trigger ('view.load_page');
    }



    // Output HTML Method
    static function Render ($view) {
        self::$options->view = THEME_PATH . '/' . $view;
        extract (get_object_vars (self::$vars));
        Plugin::Trigger ('view.render');
        include (self::$options->layout);
    }



    // Set layout to be used by renderer
    static function SetLayout ($layout) {
         self::$options->layout = THEME_PATH . '/layouts/' . $layout;
         Plugin::Trigger ('view.set_layout');
    }



    // Retrieve Header file Method
    static function Header() {
        extract (get_object_vars (self::$vars));
        Plugin::Trigger ('view.header');
        include (self::$options->header);
    }



    // Retrieve Main Body Method
    static function Body() {
        extract (get_object_vars (self::$vars));
        Plugin::Trigger ('view.body');
        include (self::$options->view);
    }



    // Retrieve Footer file Method
    static function Footer() {
        extract (get_object_vars (self::$vars));
        Plugin::Trigger ('view.footer');
        include (self::$options->footer);
    }



    // Retrieve Custom Block file Method
    static function Block ($tpl_file) {
        extract (get_object_vars (self::$vars));
        $block = THEME_PATH . '/blocks/' . $tpl_file;
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
        $block = THEME_PATH . '/blocks/' . $tpl_file;
        Plugin::Trigger ('view.repeating_block');

        foreach ($records as $_id) {
            Plugin::Trigger ('view.repeating_block_loop');
            include ($block);
        }

    }



    // Add specified block to sidebar
    static function AddSidebarBlock ($tpl_file) {
        self::$options->blocks[] = $tpl_file;
        Plugin::Trigger ('view.add_sidebar_block');
    }



    // Output sidebar blocks
    static function OutputSidebarBlocks() {
        Plugin::Trigger ('view.output_sidebar_blocks');
        foreach (self::$options->blocks as $_block) {
            Plugin::Trigger ('view.ouput_sidebar_blocks_loop');
            self::Block($_block);
        }
    }



    /**
     * Add CSS file to the document
     * @param string $css_name Filename of the CSS file to be attached
     * @return void CSS file is stored to be written in document
     */
    static function AddCss ($css_name) {
        $css_url = THEME . '/css/' . $css_name;
        self::$options->css[] = '<link rel="stylesheet" href="' . $css_url . '" />';
        Plugin::Trigger ('view.add_css');
    }



    /**
     * Write the Additional CSS tags to the browser
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
     * Add Add JS file to the document
     * @param string $js_name Filename of the JS file to be attached
     * @return void JS file is stored to be written in document
     */
    static function AddJs ($js_name) {
        $js_url = THEME . '/js/' . $js_name;
        self::$options->js[] = '<script type="text/javascript" src="' . $js_url . '"></script>';
        Plugin::Trigger ('view.add_js');
    }



    /**
     * Write the Additional JS tags to the browser
     * @return mixed JS src tags are written
     */
    static function WriteJs() {
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
        if (isset (self::$options->meta)) {
            foreach (self::$options->meta as $_value) {
                Plugin::Trigger ('view.write_meta_loop');
                echo $_value, "\n";
            }
        }
    }

}

?>