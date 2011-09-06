<?php

class App {

    /**
     * Manually Throw 404 error page
     * @global object $config Site configuration settings
     * @return void 404 page is output to buffer
     */
    static function Throw404() {
        global $config;
        include_once (DOC_ROOT . '/cc-core/controllers/system_404.php');
        exit();
    }




    /**
     * Load class from library
     * @param string $class The name of the class to be loaded
     * @param string $path [optional] Path to the class' directory. Defaults to site LIB directory
     * @return void Includes the requested class into memory if not already loaded
     */
    static function LoadClass ($class, $path = LIB) {
        if (!class_exists ($class)) {
            include ($path . "/$class.php");
        }
    }




    /**
     * Write/Append message to log file
     * @param string $log_file name of the log file to write to
     * @param string $message Message to log
     * @return void Message is written to log file
     */
    static function Log ($log_file, $message) {
        $message .= "\n";
        $handle = fopen ($log_file, 'at');
        fwrite ($handle, $message);
        fclose($handle);
    }




    /**
     * Send an email notification to the site admin
     * @param string $subject The subject of the alert email
     * @param string $body The body of the message for the alert email
     * @return void sends an alert email to site admin
     */
    static function Alert ($subject, $body) {
        App::LoadClass('Mail');
        $mail = new Mail();
        $mail->subject = $subject;
        $mail->body = $body;
        $mail->Send (Settings::Get ('admin_email'));
    }




    /**
     * Check if system is fully installed. If they are viewing "/" and system is
     * not installed they are forwared to the installer. If system is not "fully"
     * installed they are given a "Incomplete Install" message
     */
    static function InstallCheck() {

        if (!file_exists (DOC_ROOT . '/cc-core/config/config.php') && file_exists (DOC_ROOT . '/install')) {
            $PROTOCOL = (!empty ($_SERVER['HTTPS'])) ? 'https://' : 'http://';
            $HOSTNAME = $_SERVER['SERVER_NAME'];
            $PORT = ($_SERVER['SERVER_PORT'] == 80 ? '' : ':' . $_SERVER['SERVER_PORT']);
            $PATH = rtrim (dirname (preg_replace ('/\?.*/', '', $_SERVER['REQUEST_URI'])), '/');
            $HOST = $PROTOCOL . $HOSTNAME . $PORT . $PATH;
            header ("Location: $HOST/install/");
            exit();
        } else if ((!file_exists (DOC_ROOT . '/cc-core/config/config.php') || file_exists (DOC_ROOT . '/install')) && !isset ($_GET['first_run'])) {
            exit('<!DOCTYPE html><html><head><title>Incomplete Install</title><meta content="text/html;charset=utf-8" http-equiv="Content-Type"><style type="text/css">*{padding:0;margin:0;}body{background-color:#ebebeb;font-size:12px;font-family:arial,helvetica,sans-serif;color:#666;}#main{margin:200px auto 0;width:960px;}.block{margin-top:15px;border:3px solid #CCC;padding:15px;background-color:#FFF;border-radius:10px;}h1{color:#333;font-weight:bold;font-size:24px;}p{padding:5px 0;}</style></head><body><div id="main"><h1>Incomplete Install</h1><div class="block"><p>It appears the install process did not complete properly. Please re-run the installer and ensure so see it through to the end.</p></div></div></body></html>');
        }
        
    }




    /**
     * Check if a system update is in progress and display a "Maintenance" message
     * to any user attempting to access the site
     */
    static function MaintCheck() {
        if (file_exists (DOC_ROOT . '/.updates')) exit('<!DOCTYPE html><html><head><title>Maintenance</title><meta content="text/html;charset=utf-8" http-equiv="Content-Type"><style type="text/css">*{padding:0;margin:0;}body{background-color:#ebebeb;font-size:12px;font-family:arial,helvetica,sans-serif;color:#666;}#main{margin:200px auto 0;width:960px;}.block{margin-top:15px;border:3px solid #CCC;padding:15px;background-color:#FFF;border-radius:10px;}h1{color:#333;font-weight:bold;font-size:24px;}p{padding:5px 0;}</style></head><body><div id="main"><h1>Maintenance</h1><div class="block"><p>We are currently undergoing scheduled maintenance. Please try back later.</p><p>Sorry for the inconvenience.</p></div></div></body></html>');
    }




    /**
     * Check if uploads are enabled and display message if applicable
     * @global object $config
     * @return void Script is terminated, user is presented with uploads disabled message
     */
    static function EnableUploadsCheck() {
        global $config;
        if ($config->enable_uploads != '1') exit('<!DOCTYPE html><html><head><title>Uploads Disabled</title><meta content="text/html;charset=utf-8" http-equiv="Content-Type"><style type="text/css">*{padding:0;margin:0;}body{background-color:#ebebeb;font-size:12px;font-family:arial,helvetica,sans-serif;color:#666;}#main{margin:200px auto 0;width:960px;}.block{margin-top:15px;border:3px solid #CCC;padding:15px;background-color:#FFF;border-radius:10px;}h1{color:#333;font-weight:bold;font-size:24px;}p{padding:5px 0;}</style></head><body><div id="main"><h1>Uploads Disabled</h1><div class="block"><p>Your server does not meet the minimum requirements for video encoding. As a result video uploads have been disabled. Please check with your web host to ensure they fully support CumulusClips.</p><p>Visit the Admin Panel -> Settings -> Video, to re-check your system and enable uploads. You could also use a plugin to manage video encoding for you.</p></div></div></body></html>');
    }




    /**
     * Check for mobile devices or mobile site opt-out. If a mobile device is
     * detected via it's user agent header they will be sent to the mobile version
     * of the site unless they're already viewing the mobile site or have opted
     * out from it.
     */
    static function MobileCheck() {

        // Verify if user opted-out from Mobile site
        if (isset ($_GET['nomobile'])) {
            setcookie ('nomobile', md5('nomobile'), time()+3600*24*3);
        }

        // Check for mobile devices and if has opted out from mobile site
        $agent = '/ip(ad|hone|od)|android/i';
        if (isset ($_SERVER['HTTP_USER_AGENT']) && preg_match ($agent, $_SERVER['HTTP_USER_AGENT']) && !isset ($_COOKIE['nomobile']) && !isset ($_GET['nomobile'])) {

            // Verify user isn't already viewing mobile site
            if (!isset ($_GET['mobile'])) {
                header ("Location: " . MOBILE_HOST . "/");
                exit();
            }

        }

    }




    /**
     * Determine which theme should be used
     * @return string Theme to be used
     */
    static function CurrentTheme() {

        $preview_theme = false;

        // Determine active theme
        if (isset ($_GET['mobile'])) {
            $active_theme = Settings::Get ('active_mobile_theme');
        } else {
            $active_theme = Settings::Get ('active_theme');
        }

        // Check if 'Preview' theme was provided
        if (isset ($_GET['preview_theme']) && Functions::ValidTheme ($_GET['preview_theme'])) {
            $active_theme = $_GET['preview_theme'];
            $preview_theme = $_GET['preview_theme'];
        }

        define ('PREVIEW_THEME', $preview_theme);
        return $active_theme;
        
    }




    /**
     * Determine which language should be used
     * @return string Language to be used
     */
    static function CurrentLang() {

        $preview_lang = false;
        $default_lang = Settings::Get ('default_language');

        // Check if user selected language
        if (isset ($_SESSION['user_lang']) && file_exists (DOC_ROOT . '/cc-content/languages/' . $_SESSION['user_lang'] . '.xml')) {
            $default_lang = $_SESSION['user_lang'];
        }

        // Check if 'Preview' language was provided
        if (isset ($_GET['preview_lang']) && file_exists (DOC_ROOT . '/cc-content/languages/' . $_GET['preview_lang'] . '.xml')) {
            $default_lang = $_GET['preview_lang'];
            $preview_lang = $_GET['preview_lang'];
        }

        define ('PREVIEW_LANG', $preview_lang);
        return $default_lang;

    }

}

?>