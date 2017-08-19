<?php

class Functions
{
    /**
     * Return the values from a single column in the input array
     * Used for PHP versions prior to PHP 5.5
     * @see http://us2.php.net/manual/en/function.array-column.php
     * @param array $array A multi-dimensional array (record set) from which to pull a column of values.
     * @param mixed $columnKey The column of values to return.
     * @return array Returns an array of values representing a single column from the input array.
     */
    public static function arrayColumn(array $array, $columnKey = 0)
    {
        $newArray = array();
        foreach ($array as $child) {
            if (is_array($child)) {
                $newArray[] = $child[$columnKey];
            } else if (is_object($child)) {
                $newArray[] = $child->$columnKey;
            } else {
                throw new Exception('Invalid child data type passed to arrayColumn');
            }
        }
        return $newArray;
    }

    /**
     * Filters an array based on the value of a given column
     *
     * @param mixed $searchValue The value to filter by
     * @param string $column The column to filter by
     * @param array $array List of objects or associative arrays to filter
     * @return array Returns an array consiting of objects/arrays whose column value was a match
     */
    public static function arrayColumnFilter($searchValue, $column, array $array)
    {
        return array_values(array_filter($array, function($value) use ($column, $searchValue) {
            $object = (object) $value;
            return ($object->{$column} == $searchValue);
        }));
    }

    /**
     * Create a slug based on given string
     * @param string $string
     * @return string URL/Slug version of string
     */
    public static function createSlug($string)
    {
        $slug = strtolower (preg_replace ('/[^a-z0-9]+/i', '-', $string));
        $slug = preg_replace ('/^-|-$/', '', $slug);
        return $slug;
    }

    /**
     * Extract the file extension of the given file
     * @param string $filename Filename to examine extension for
     * @return string|boolean The file extension if one is present, boolean false
     * if none is found.
     */
    public static function getExtension($filename)
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        return (empty($extension)) ? false : strtolower($extension);
    }

    /**
     * Redirect user if based on a condition's value
     * @param mixed $condition Condition to be tested
     * @param string $redirect Location to send user if condition evaluates to empty
     * @return void If condition evaluates to empty value user is redirected, nothing otherwise
     */
    public static function redirectIf($condition, $redirect)
    {
        if (isset($_GET['preview_lang'])) $redirect = Functions::appendQueryString($redirect, array('preview_lang' => $_GET['preview_lang']));
        if (isset($_GET['preview_theme'])) $redirect = Functions::appendQueryString($redirect, array('preview_theme' => $_GET['preview_theme']));
        if (empty ($condition)) {
            header("Location: $redirect");
            exit();
        }
    }

    /**
     * Generate a string of random characters, numbers, and special characters
     * @param integer $length of string to be returned
     * @param boolean $special Whether or not to allow special characters
     * @return string String of random characters
     */
    public static function random($length, $special = null)
    {
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
     * Format video duration into seconds
     *
     * @deprecated Depracated in 2.5, removed in 2.6. Use self::durationToSeconds instead
     */
    public static function durationInSeconds($duration)
    {
        return static::durationToSeconds($duration);
    }

    /**
     * Converts from duration format (hh:mm:ss) into seconds
     *
     * @param string $duration Duration in hh:mm:ss or mm:ss format
     * @return integer Total seconds
     */
    public static function durationToSeconds($duration)
    {
        $str_time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $duration);
        sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);
        return $hours * 3600 + $minutes * 60 + $seconds;
    }

    /**
     * Shortens duration format (hh:mm:ss) into display friendly duration format
     *
     * Extra "0" and ":" are trimmed up until a m:ss minimum
     *
     * @param string $duration The duration to be formatted
     * @return string Returns formatted duration
     */
    public static function formatDuration($duration)
    {
        $newDuration = ltrim($duration, '0:');
        if (strlen($newDuration) == 2) {
            return '0:' . $newDuration;
        } else if (strlen($newDuration) == 1) {
            return '0:0' . $newDuration;
        } else {
            return $newDuration;
        }
    }

    /**
     * Truncate a string at desired length
     * @param string $string String to be truncated
     * @param integer $max_length Max length to allow before truncation
     * @return string Returns truncated string with '...' appended to the end.
     * If string is shorter than max length then original string is returned.
     */
    public static function cutOff($string, $max_length)
    {
        if (strlen ($string) > $max_length) {
            $short = $max_length - 3;
            return substr ($string,0,$short) . '...';
        } else {
            return $string;
        }
    }

    /**
     * Determine elapsed time string for given timestamp
     * @param integer $timestamp Timestamp to check elapsed time for
     * @return string Amount of time passed since given timestamp. If elapsed
     * time is greater than 24 hours, then just return the date is returned
     */
    public static function timeSince($timestamp)
    {
        $secondsSinse = time()-$timestamp;
        $tokens = array (
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            60 => 'minute',
            1 => 'second'
        );

        foreach ($tokens as $unit => $text) {
            if ($secondsSinse < $unit) continue;
            $numberOfUnits = floor($secondsSinse / $unit);
            if ($text == 'second') {
                return 'Just now';
            } else {
                return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '') . ' ago';
            }
        }
    }

    /**
     * Search and replace placeholders with strings
     * @param string $string The string to perform replacements on
     * @param array $replace The values of this array will replace any
     * placeholders/variables in the string. The key of the array replaces
     * variables of the same name (not case sen.) with the value from the array. If the
     * variable is a link, the value from the array is treated as the URL for the href attribute
     * @return string The given string is returned with replacements made to it
     */
    public static function replace($string, $replace)
    {
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

    /**
     * Check admin settings cookie to see if sidebar panel is open
     * @param string $panel The panel to be checked if openned or not
     * @return boolean Returns true if panel is open, false otherwise
     */
    public static function isPanelOpen($panel)
    {
        if (!empty ($_COOKIE['cc_admin_settings'])) {
            parse_str ($_COOKIE['cc_admin_settings'], $settings);
            if (!empty ($settings[$panel]) && $settings[$panel] == '1') {
                return true;
            }
        }
        return false;
    }

    /**
     * Output loaded JS files to browser for the admin panel
     * @global array $admin_js List of JS file to be printed
     * @return mixed All JS file entries are printed with javascript tags
     */
    public static function adminOutputJS()
    {
        global $admin_js;
        if (!isset ($admin_js)) return false;
        foreach ($admin_js as $file) {
            echo '<script type="text/javascript" src="' . $file . '"></script>' . "\n";
        }
    }

    /**
     * Output loaded meta tags to browser for the admin panel
     * @global array $admin_meta List of meta tags to be printed
     * @return mixed All meta tag entries are printed in the document head
     */
    public static function AdminOutputMeta() {
        global $admin_meta;
        if (!isset ($admin_meta)) return false;
        foreach ($admin_meta as $name => $content) {
            echo '<meta name="' . $name . '" content="' . $content . '" />' . "\n";
        }
    }

    /**
     * Output loaded CSS files to browser for the admin panel
     * @global array $admin_css List of CSS files to be printed
     * @return mixed All CSS file entries are printed in document head
     */
    public static function adminOutputCss()
    {
        global $admin_css;
        if (!isset ($admin_css)) return false;
        foreach ($admin_css as $file) {
            echo '<link rel="stylesheet" type="text/css" href="' . $file . '" />' . "\n";
        }
    }

    /**
     * "Phone home" to check if updates are available
     * @return object|boolean Returns update object if its available, false otherwise
     */
    public static function updateCheck()
    {
        $version = urlencode(CURRENT_VERSION);
        $client = urlencode($_SERVER['REMOTE_ADDR']);
        $system = urlencode($_SERVER['SERVER_ADDR']);
        $update_url = MOTHERSHIP_URL . "/updates/?version=$version&client=$client&system=$system";

        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $update_url);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true);
        $update = curl_exec($curl_handle);
        curl_close($curl_handle);

        $json = json_decode($update);
        return (!empty($update) && !empty($json) && (version_compare(CURRENT_VERSION, $json->version) === -1)) ? $json : false;
    }

    /**
     * Validate a given theme name
     * @param string $theme_name The name of the theme to validate
     * @return boolean Returns true if theme is valid false otherwise
     */
    public static function validTheme($theme_name)
    {
        if (!empty ($theme_name) && file_exists (THEMES_DIR . "/$theme_name/theme.xml")) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Append query string parameters to a URL
     * @param string $url The original URL
     * @param array $query Query string parameters in Key/Value pairs
     * @return string Returns the URL with the query string appended to it
     */
    public static function appendQueryString($url, $query)
    {
        // Break URL into parts
        $parts = parse_url ($url);

        // Append/Add query string
        $new_query = ((isset ($parts['query'])) ? '&' : '?') .  http_build_query ($query);

        // Append query string before #frament if any
        if (isset ($parts['fragment'])) {
            $split = explode ('#', $url);
            $url = $split[0] . $new_query . '#' . $split[1];
        } else {
            $url .= $new_query;
        }

        return $url;
    }

    /**
     * Retrieve list of supported video types in a requested formats
     * @global object $config Site configuration settings
     * @param string $output_format [optional] Format to output the list of supported video types
     * @return string|array Returns list of supported video types in requested format
     */
    public static function getVideoTypes($output_format = 'array')
    {
        global $config;
        $type = array();
        $type['array'] = $config->acceptedVideoFormats;
        $type['fileDesc'] = $type['fileExt'] = '';
        foreach ($type['array'] as $value) {
            $format = "*.$value";
            $type['fileDesc'] .= " ($format)";
            $type['fileExt'] .= ";$format";
        }
        return $type[$output_format];
    }

    /**
     * Converts a GMT/UTC date into local time
     * @param string $date_time A GMT date to be converted. Any format accepted by PHP strtotime()
     * @param string $format Format to output time in. See PHP date() for formatting options
     * @return string Returns date string formatted by given format
     */
    public static function gmtToLocal($date_time, $format = 'Y-m-d G:i:s')
    {
        $date = ($date_time instanceof \DateTime) ? $date_time : new \DateTime($date_time, new \DateTimeZone('UTC'));
        $date->setTimezone(new DateTimeZone(date_default_timezone_get()));
        return $date->format($format);
    }

    /**
     * Calculates elapsed time between given dates
     *
     * @param \DateTime $dateStart Starting date
     * @param \DateTime $dateEnd (optional) Ending date, uses "now" if ommited
     * @return string Returns elapsed time in format "D Days HH:MM:SS"
     */
    public function getTimeSince(\DateTime $dateStart, \DateTime $dateEnd = null)
    {
        $display = '';

        // Retrieve current date/time
        $endDateTime = ($dateEnd) ?: new \DateTime('now', new \DateTimeZone('UTC'));

        $remainingSeconds = $endDateTime->getTimestamp() - $dateStart->getTimestamp();

        // Generate days
        if ($remainingSeconds >= 86400) {
            $days = floor($remainingSeconds/86400);
            $display = $days . ' Days ';
            $remainingSeconds = $remainingSeconds%86400;
        }

        // Generate hours
        if ($remainingSeconds >= 3600) {
            $hours = floor($remainingSeconds/3600);
            $display .= str_pad($hours, 2, "0", STR_PAD_LEFT) . ':';
            $remainingSeconds = $remainingSeconds%3600;
        } else {
            $display .= '00:';
        }

        // Generate minutes
        if ($remainingSeconds >= 60) {
            $minutes = floor($remainingSeconds/60);
            $display .= str_pad($minutes, 2, '0', STR_PAD_LEFT) . ':';
            $remainingSeconds = $remainingSeconds%60;
        } else {
            $display .= '00:';
        }

        // Generate seconds
        if ($remainingSeconds > 0) {
            $display .= str_pad($remainingSeconds, 2, '0', STR_PAD_LEFT);
        } else {
            $display .= '00';
        }

        return $display;
    }

    /**
     * Formats bytes into a human readable format
     *
     * @param int $bytes Total number of bytes
     * @param int $decimal Number of decimal places to include result
     * @return string Returns human readable formatted bytes
     */
    public static function formatBytes($bytes, $decimals = 2)
    {
        $size = array('B','KB','MB','GB','TB','PB','EB','ZB','YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . '' . @$size[$factor];
    }
}