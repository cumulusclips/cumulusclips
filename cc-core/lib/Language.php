<?php

class Language {

    static private $xml;



    /**
     * Load a language xml file into a SimpleXML Object for use in this class
     * @param string $language Language to be loaded
     * @return void The given language xml file is stored into memory
     */
    static function LoadLangPack ($language) {
        $lang_file = DOC_ROOT . '/cc-content/languages/' . $language . '.xml';
        self::$xml = simplexml_load_file ($lang_file);
    }



    /**
     * Retrieve text from the loaded language xml file
     * @param string $node The name of the language xml node to retrieve
     * @param array $replace Optional Replacements for placeholders (See Functions::Replace for more deails)
     * @return string The requested string is returned with replacements made
     * to it or boolean false if requested node is invalid
     */
    static function GetText ($node, $replace = array()) {

        // Retrieve node text if it exists
        if (isset (self::$xml->terms->$node)) {

            $string = self::$xml->terms->$node;

            // Check for text replacements
            if (!empty ($replace)) {
                $string = Functions::Replace ($string, $replace);
            }

            return $string;

        } else {
            return false;
        }        

    }



    /**
     * Retrieve custom text from the loaded language xml file
     * @param string $node The name of the custom language xml node to retrieve
     * @param array $replace Optional Replacements for placeholders (See Functions::Replace for more deails)
     * @return string The requested string is returned with replacements made
     * to it or boolean false if requested node is invalid
     */
    static function GetCustomText ($node, $replace = array()) {

        // Retrieve node text if it exists
        if (isset (self::$xml->custom->$node)) {

            $string = self::$xml->custom->$node;

            // Check for text replacements
            if (!empty ($replace)) {
                $string = Functions::Replace ($string, $replace);
            }

            return $string;

        } else {
            return false;
        }

    }



    /**
     * Output the formal English language name of the loaded language
     * @return string The language name in the language xml file
     */
    static function GetLanguage() {
        return self::$xml->information->language_name;
    }



    /**
     * Output the locale code of the loaded language
     * @return string The locale code in the language xml file
     */
    static function GetLocale() {
        return self::$xml->information->locale;
    }



    /**
     * Output the CSS-friendly name of the loaded language for use in stylesheets
     * @return string The CSS language name in the language xml file
     */
    static function GetCSSName() {
        return self::$xml->information->css_name;
    }



    /**
     * Retrieve all the meta information for a given page
     * @param string $page Page whose information to look up
     * @return object Returns simple xml object representing that page's node
     */
    static function GetMeta ($page) {
        return self::$xml->meta->$page;
    }

}

?>