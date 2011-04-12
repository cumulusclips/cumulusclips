<?php

class Language {

    static private $xml;

    static function LoadLangPack ($language) {
        $lang_file = DOC_ROOT . '/cc-content/languages/' . $language . '.xml';
        self::$xml = simplexml_load_file ($lang_file);
    }



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

}

?>