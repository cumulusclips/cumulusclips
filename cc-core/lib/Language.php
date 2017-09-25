<?php

/**
 * Language manager
 *
 * @package CumulusClips
 * @subpackage Language
 * @copyright Copyright (c) 2011-2016 CumulusClips (http://cumulusclips.org)
 * @license http://cumulusclips.org/LICENSE.txt GPL Version 2
 */
class Language
{
    /**
     * @var string System name of the currently loaded language
     */
    protected static $systemName = '';

    /**
     * @var array List of language entries keyed by the entry name
     */
    protected static $entries = array();

    /**
     * @var array List of custom language entries keyed by the entry name
     */
    protected static $customEntries = array();

    /**
     * @var stdClass Information about currently loaded language
     */
    protected static $information;

    /**
     * Load a language pack into memory
     * @return void The given language pack is stored into memory
     */
    public static function init()
    {
        $activeLanguage = Settings::get('default_language');

        // Retrieve installed languages
        $installedLanguages = self::getInstalled();

        // Check if user selected language
        if (
            isset($_COOKIE['user_lang'])
            && isset($installedLanguages->{$_COOKIE['user_lang']})
            && $installedLanguages->{$_COOKIE['user_lang']}->active
        ) {
            $activeLanguage = $_COOKIE['user_lang'];
        }

        // Check if 'Preview' language was provided
        if (
            isset($_GET['preview_lang'])
            && isset($installedLanguages->{$_GET['preview_lang']})
        ) {
            $activeLanguage = $_GET['preview_lang'];
            define('PREVIEW_LANG', $activeLanguage);
        }

        // Load active language pack into memory
        $languageFileEntries = self::loadEntries(
            DOC_ROOT . '/cc-content/languages/' . $activeLanguage . '.xml'
        );
        self::$systemName = $activeLanguage;
        self::$information = (object) $languageFileEntries['information'];
        self::$entries = $languageFileEntries['terms'];

        // Verify entries were loaded
        if (empty(self::$entries)) {
            exit('CC-ERROR-300 CumulusClips has encountered an error and cannot continue.');
        }

        // Retrieve custom language entries
        $textService = new TextService();
        $customEntries = $textService->getLanguageEntries($activeLanguage);

        // Parse language entries for final text values
        foreach ($customEntries as $entry) {
            self::$customEntries[$entry->name] = $entry->content;
        }
    }

    /**
     * Loads given XML language pack file and converts it to any array
     * @param string $languageFile Absolute file path to language file to load
     * @return array Returns language file's text entries in array format
     */
    protected static function loadLanguageFile($languageFile)
    {
        // Load XML file contents into memory
        $fileContents = file_get_contents($languageFile);
        $strippedOfComments = preg_replace('/<\!--.*?-->/', '', $fileContents);
        $xml = new SimpleXMLElement($strippedOfComments, LIBXML_NOCDATA + LIBXML_COMPACT);

        $languageEntries = array();

        // Strip spaces and duplicate entries from general entries
        $languageEntries['terms'] = array();
        foreach ((array) $xml->terms as $key => $value) {
            $text = (is_array($value)) ? $value[0] : $value;
            $languageEntries['terms'][$key] = preg_replace(
                array('/^\s+/', '/\s{2,}/', '/\s+$/'),
                array('', ' ', ''),
                $text
            );
        }

        // Clean up information entries if available
        if (isset($xml->information)) {
            $languageEntries['information'] = preg_replace(
                array('/^\s+/', '/\s{2,}/', '/\s+$/'),
                array('', ' ', ''),
                (array) $xml->information
            );
        }

        return $languageEntries;
    }

    /**
     * Retrieves an entry from the loaded language pack
     * @param string $key The name of the language entry to retrieve
     * @param array $replace Optional Replacements for placeholders (See Functions::Replace for more deails)
     * @return string|boolean Returns value of requested entry with replacements made, boolean false if entry is doesn't exists
     */
    public static function getText($key, $replace = array())
    {
        // Retrieve entry text if it exists
        if (!empty(self::$customEntries[$key])) {
            $string = self::$customEntries[$key];
        } elseif (!empty(self::$entries[$key])) {
            $string = self::$entries[$key];
        } else {
            return false;
        }

        // Perform any text replacements
        if (!empty($replace)) {
            $string = Functions::replace($string, $replace);
        }

        return $string;
    }

    /**
     * Retrieves all the meta information (title, keywords, description) for a given page
     * @param string $page Page whose information to look up
     * @return stdClass Returns object with meta fields as the property names, i.e. $meta->keywords
     */
    public static function getMeta($page)
    {
        // Locate meta entries for given page
        $termKeys = array_keys(self::$entries);
        $pageMetaKeys = array_flip(array_filter($termKeys, function($value) use ($page) {
            return (strpos($value, 'meta.' . $page . '.') === 0) ? true : false;
        }));

        // Extract page's meta entries from entries list and from custom entries list
        $originalMeta = array_intersect_key(self::$entries, $pageMetaKeys);
        $customMeta = array_intersect_key(self::$customEntries, $pageMetaKeys);

        // Overwrite XML entries with custom entries
        $combinedEntries = array_replace($originalMeta, $customMeta);

        // Structure entries into object
        $meta = new stdClass();
        foreach ($combinedEntries as $key => $value) {
            $keyParts = explode('.', $key);
            $fieldName = array_pop($keyParts);
            $meta->{$fieldName} = $value;
        }

        return $meta;
    }

    /**
     * Retrieves locale of the currently loaded language file
     * @return string Returns HTML/W3C friendly language locale
     */
    public static function getLocale()
    {
        return str_replace('_', '-', self::$systemName);
    }

    /**
     * Retrieves the name of currently loaded language
     * @param boolean $native (optional) Return language's native name rather than it's system name
     * @return string The name of the loaded language is returned
     */
    public static function getLanguage($native = false)
    {
        return ($native) ? self::$information->native_name : self::$systemName;
    }

    /**
     * Retrieves the CSS-friendly name of the loaded language for use in stylesheets
     * @return string The CSS name of the loaded language pack is returned
     */
    public static function getCSSName()
    {
        return self::$systemName;
    }

    /**
     * Retrieve a list of active languages
     * @return array Returns a list of active languages
     */
    public static function getActiveLanguages()
    {
        $active = array();
        $installed = self::getInstalled();
        foreach ($installed as $systemName => $language) {
            if ($language->active) {
                $active[$systemName] = $language;
            }
        }
        return $active;
    }

    /**
     * Retrieves list of installed language packs keyed by language's system name
     * @return stdClass Returns hash of installed languages
     */
    public static function getInstalled()
    {
        return json_decode(Settings::get('installed_languages'));
    }

    /**
     * Installs and activates a language pack
     * @param string $language System name of the language being installed
     * @return stdClass Returns hash of resulting installed languages
     */
    public static function install($language)
    {
        // Verify language file exists
        $languageFile = DOC_ROOT . '/cc-content/languages/' . $language . '.xml';
        if (!file_exists($languageFile)) {
            throw new Exception('Language file does not exist');
        }

        // Check if language is already installed
        $installedLanguages = self::getInstalled();
        if (isset($installedLanguages->{$language})) {
            return $installedLanguages;
        }

        // Load language pack into memory
        $entries = self::loadLanguageFile($languageFile);

        // Save new language pack
        $installedLanguages->{$language} = (object) array(
            'active' => true,
            'lang_name' => $entries['information']['lang_name'],
            'native_name' => $entries['information']['native_name'],
            'author' => (!empty($entries['information']['author'])) ? $entries['information']['author'] : '',
            'sample' => $entries['information']['sample'],
            'version' => (!empty($entries['information']['version'])) ? $entries['information']['version'] : '',
            'update' => (!empty($entries['information']['update'])) ? $entries['information']['update'] : '',
            'notes' => (!empty($entries['information']['notes'])) ? $entries['information']['notes'] : ''
        );
        self::save($installedLanguages);

        return $installedLanguages;
    }

    /**
     * Removes a language pack from the system
     * @param string $language System name of the language being uninstalled
     * @return stdClass Returns hash of remaining installed languages
     * @throws Exception Thrown if given language is site or system default, or if language is not currently installed
     */
    public static function uninstall($language)
    {
        // Verify system default isn't being uninstalled
        if ($language === 'en_US') {
            throw new Exception('Cannot uninstall system default language');
        }

        // Verify default language isn't being uninstalled
        $defaultLanguage = Settings::get('default_language');
        if ($language === $defaultLanguage) {
            throw new Exception('Cannot uninstall default language. Make another language the default first then try again.');
        }

        // Verify language is installed
        $installedLanguages = self::getInstalled();
        if (!isset($installedLanguages->{$language})) {
            throw new Exception('Language is not currently installed.');
        }

        // Remove associated custom language entries
        $textMapper = new TextMapper();
        $customEntries = $textMapper->getMultipleByCustom(array(
            'type' => TextMapper::TYPE_LANGUAGE,
            'language' => $language
        ));
        foreach ($customEntries as $entry) {
            $textMapper->delete($entry->textId);
        }

        // Save updated language list
        unset($installedLanguages->{$language});
        self::save($installedLanguages);

        // Verify language file exists
        $languageFile = DOC_ROOT . '/cc-content/languages/' . $language . '.xml';
        if (file_exists($languageFile)) {
            Filesystem::delete($languageFile);
        }

        return $installedLanguages;
    }

    /**
     * Saves installed languages
     * @param stdClass $installedLanguages Hash of installed languages to be saved
     * @return void Installed languages are saved
     */
    public static function save($installedLanguages)
    {
        Settings::set('installed_languages', json_encode($installedLanguages));
    }

    /**
     * Loads the given theme's language entries for the active language
     *
     * @param string $theme System name of theme to load language pack for
     * @return void Theme's language file is loaded for active language
     */
    public static function loadThemeLanguage($theme)
    {
        $themeLanguagePath = THEMES_DIR . '/' . $theme . '/languages/';
        $themeEntries = self::loadEntries(
            $themeLanguagePath . self::$systemName . '.xml'
        );

        // If theme language entries exist overwrite the general entries with them
        if (isset($themeEntries['terms'])) {
            self::$entries = array_replace_recursive(self::$entries, $themeEntries['terms']);
        }
    }

    /**
     * Loads the given plugin's language entries for the active language
     *
     * @param string $pluginName System name of plugin to load language pack for
     * @return void Plugin's language file is loaded for active language
     */
    public static function loadPluginLanguage($pluginName)
    {
        $pluginLanguagePath = DOC_ROOT . '/cc-content/plugins/' . $pluginName . '/languages/';
        $pluginEntries = self::loadEntries(
            $pluginLanguagePath . self::$systemName . '.xml'
        );

        // If plugin language entries exist overwrite the general entries with them
        if (isset($pluginEntries['terms'])) {
            self::$entries = array_replace_recursive(self::$entries, $pluginEntries['terms']);
        }
    }

    /**
     * Loads and extracts language entries from given languange XML file
     * @param string $languageFile The path to the language file to load
     * @return array Returns list of entries keyed by entry name
     */
    public static function loadEntries($languageFile)
    {
        $entries = array();

        $path = dirname($languageFile);
        $language = basename($languageFile, '.xml');

        // Load system language file into memory
        $systemLanguageFile = $path . '/en_US.xml';
        if (file_exists($systemLanguageFile)) {
            $entries = self::loadLanguageFile($systemLanguageFile);
        } else {
            return $entries;
        }

        // Load active language pack into memory if different from system language
        $languageFile = $path. '/' . $language . '.xml';
        if ($language !== 'en_US' && file_exists($languageFile)) {
            $entries = array_replace_recursive($entries, self::loadLanguageFile($languageFile));
        }

        return $entries;
    }
}