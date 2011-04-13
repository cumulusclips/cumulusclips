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
     * @param array $replace Optional The values of this array will replace any
     * placeholders/variables in the language string. The key of the array replaces
     * variables of the same name (not case sen.) with the value from the array. If the
     * variable is a link, the value from the array is treated as the URL for the href attribute
     * @return string The requested string is returned with replacements made
     * to it or boolean false if requested node is invalid
     */
    static function GetText ($node, $replace = array()) {

        // Retrieve node text if it exists
        if (isset (self::$xml->terms->$node)) {

            $string = self::$xml->terms->$node;

            // Check for text replacements
            if (!empty ($replace)) {

                // Loop through and execute replacements
                foreach ($replace as $_key => $_value) {

                    // Patterns
                    $anchor_pattern = "/\{$_key\}(.*?)\{\/$_key\}/i";
                    $string_pattern = "/\{$_key\}/i";

                    // Replace anchors
                    if (preg_match ($anchor_pattern, $string, $matches)) {
                        $anchor = '<a href="' . $_value . '" title="' . $matches[1] . '">' . $matches[1] . '</a>';
                        $string = preg_replace ($anchor_pattern, $anchor, $string);
                    }

                    // Replace normal strings
                    else if (preg_match ($string_pattern, $string, $matches)) {
                        $string = preg_replace ($string_pattern, $_value, $string);
                    }

                }   // END replacements

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

}

?>