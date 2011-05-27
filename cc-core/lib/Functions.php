<?php

class Functions {

    /**
     * Create a slug based on given string
     * @param string $string
     * @return string URL/Slug version of string
     */
    static function CreateSlug ($string) {
        $slug = strtolower (preg_replace ('/[^a-z0-9]+/i', '-', $string));
        $slug = substr($slug, 0, 1) == '-' ? substr($slug,1) : $slug;
        $slug = substr($slug, -1) == '-' ? substr($slug,0, -1) : $slug;
        return $slug;
    }



    /**
     * Extract the file extension of the given file
     * @param string $filename Filename to examine extension for
     * @return string|boolean The file extension if one is present, boolean false
     * if none is found.
     */
    static function GetExtension ($filename) {
        if (!strrpos ($filename, '.')) return false;
        $filename_sections = explode ('.', $filename);
        return array_pop ($filename_sections);
    }



    /**
     * Generate a string of random characters, numbers, and special characters
     * @param integer $length of string to be returned
     * @param boolean $special Whether or not to allow special characters
     * @return string String of random characters
     */
    static function Random ($length, $special = NULL) {

        $string = '';
        $upper = range ('A','Z');
        $lower = range ('a','z');
        $digit = range (0,9);

        if ($special) {
            $special = array ('!','@','#','$','%','^','&','*','+','?','.','~','=');
        } else {
            $special = array();
        }
        $mixed = array_merge ($upper, $lower, $digit, $special);

        for ($x = 1; $x <= $length; $x++) {
            shuffle ($mixed);
            $max = count ($mixed) - 1;
            $y = rand (0, $max);
            $string .= $mixed[$y];
        }

        return $string;
    }



    /**
     * Format seconds into hr:min:sec
     * @param integer $seconds
     * @return string formated string of seconds
     */
    static function FormatSeconds ($seconds) {
        $seconds = floor ($seconds);
        $hours = str_pad (floor ($seconds/3600), 2, 0, STR_PAD_LEFT);
        $minutes = str_pad (floor ($seconds/60), 2, 0, STR_PAD_LEFT);
        $secs = str_pad ($seconds%60, 2, 0, STR_PAD_LEFT);
        return $hours . ':' . $minutes . ':' . $secs;
    }



    /**
     * Format video duration into seconds
     * @param string $duration in hh:mm:ss or mm:ss format
     * @return integer Total seconds
     */
    static function DurationInSeconds ($duration) {

        if (eregi ('^[0-9]{2}:[0-9]{2}:[0-9]{2}$', $duration)) {

            $total = 0;
            $hours = (int) substr ($duration, 0, 2);
            $minutes = (int) substr ($duration, 3, 2);
            $seconds = (int) substr ($duration, -2);
            $total += $hours*3600;
            $total += $minutes*60;
            $total += $seconds;
            return $total;

        } elseif (eregi ('^[0-9]{2}:[0-9]{2}$', $duration)) {

            $total = 0;
            $minutes = (int) substr ($duration, 0, 2);
            $seconds = (int) substr ($duration, -2);
            $total += $minutes*60;
            $total += $seconds;
            return $total;

        } else {
            return FALSE;
        }

    }
    
    
    
    /**
     * Truncate a string at desired length
     * @param string $string String to be truncated
     * @param integer $max_length Max length to allow before truncation
     * @return string Returns truncated string with '...' appended to the end.
     * If string is shorter than max length then original string is returned.
     */
    static function CutOff ($string, $max_length) {

        if (strlen ($string) > $max_length) {
            $short = $max_length - 3;
            return substr ($string,0,$short) . '...';
        } else {
            return $string;
        }

    }



    /**
     * Determine elapsed time string for given timestamp
     * @param int Timestamp to check elapsed time for
     * @return string Amount of time passed since given timestamp. If elapsed
     * time is greater than 24 hours, then just return the date is returned
     */
    static function TimeSince ($time_to_check) {

        // Time vars
        $day = 86400;
        $hour = 3600;
        $minute = 60;

        // Calculate elapsed time
        $seconds_since = time()-$time_to_check;


        // Detect if elapsed time is greater than 1 day
        if ($seconds_since < $day) {

            // Detect if elapsed time is greater than 1 hour
            if ($seconds_since < $hour) {

                // Detect if elapsed time is greater than 1 minute
                if ($seconds_since < $minute) {

                    // Time elapsed is less than 1 Minute
                    $plural = $seconds_since == 1 ? '' : 's';
                    return "$seconds_since  second$plural ago";

                } else {

                    // Time elapse is 1 Minute or greater
                    $minutes_diff = floor($seconds_since/$minute);
                    $plural = $minutes_diff == 1 ? '' : 's';
                    return "$minutes_diff minute$plural ago";

                }

            } else {

                // Time elapsed is 1 Hour or greater
                $hours_diff = floor($seconds_since/$hour);
                $plural = $hours_diff == 1 ? '' : 's';
                return  "$hours_diff hour$plural ago";

            }

        } else {
            // Time elapsed is 1 Day or greater
            return date('M d, Y', $time_to_check);
        }

    }



    /**
     * Search and replace placeholders with strings
     * @param string $node The string to perform replacements on
     * @param array $replace The values of this array will replace any
     * placeholders/variables in the string. The key of the array replaces
     * variables of the same name (not case sen.) with the value from the array. If the
     * variable is a link, the value from the array is treated as the URL for the href attribute

     * @return string The given string is returned with replacements made to it
     */
    static function Replace ($string, $replace) {

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

        return $string;

    }



    static function IsPanelOpen ($panel) {
        if (!empty ($_COOKIE['cc_admin_settings'])) {
            parse_str ($_COOKIE['cc_admin_settings'], $settings);
            if (!empty ($settings[$panel]) && $settings[$panel] == 'open') {
                return true;
            } else {
                return false;
            }
        }
    }

}

?>