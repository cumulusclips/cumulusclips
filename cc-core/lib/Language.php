<?php

class Language
{
    static private $xml;

    /**
     * Load a language xml file into a SimpleXML Object for use in this class
     * @param string $language Language to be loaded
     * @return void The given language xml file is stored into memory
     */
    public static function loadLangPack($language)
    {
        $lang_file = DOC_ROOT . '/cc-content/languages/' . $language . '.xml';
        self::$xml = simplexml_load_file($lang_file);
    }

    /**
     * Retrieve text from the loaded language xml file
     * @param string $node The name of the language xml node to retrieve
     * @param array $replace Optional Replacements for placeholders (See Functions::Replace for more deails)
     * @return string The requested string is returned with replacements made
     * to it or boolean false if requested node is invalid
     */
    public static function getText($node, $replace = array())
    {
        // Retrieve node text if it exists
        if (isset(self::$xml->terms->$node)) {

            $string = self::$xml->terms->$node;

            // Check for text replacements
            if (!empty($replace)) {
                $string = Functions::replace($string, $replace);
            }

            return (string) $string;
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
    public static function getCustomText($node, $replace = array())
    {
        // Retrieve node text if it exists
        if (isset(self::$xml->custom->$node)) {

            $string = self::$xml->custom->$node;

            // Check for text replacements
            if (!empty($replace)) {
                $string = Functions::replace($string, $replace);
            }

            return $string;
        } else {
            return false;
        }
    }

    /**
     * Output the formal human readable name of the loaded language
     * @param boolean $native (optional) Whether or not to return the native translation for the current language
     * @return string The language name in the language xml file
     */
    public static function getLanguage($native = false)
    {
        return ($native) ? self::$xml->information->native_name : self::$xml->information->lang_name;
    }

    /**
     * Output the CSS-friendly name of the loaded language for use in stylesheets
     * @return string The CSS language name in the language xml file
     */
    public static function getCSSName()
    {
        return self::$xml->information->css_name;
    }

    /**
     * Retrieve all the meta information for a given page
     * @param string $page Page whose information to look up
     * @return object Returns simple xml object representing that page's node
     */
    public static function getMeta($page)
    {
        return (empty(self::$xml->meta->$page)) ? new stdClass() : self::$xml->meta->$page;
    }

    /**
     * Retrieve a list of active languages
     * @return array Returns a list of active languages
     */
    public static function getActiveLanguages()
    {
        $active = Settings::get('active_languages');
        return json_decode($active);
    }
}