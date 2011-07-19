<?php

class App {

    /**
     * Manually Throw 404 error page
     * @global object $config Site configuration settings
     * @return void 404 page is output to buffer
     */
    static function Throw404() {
        global $config;
        include (DOC_ROOT . '/cc-core/controllers/system_404.php');
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
        $from = 'From: CumulusClips <' . SITE_EMAIL . '>';
        @mail (MAIN_EMAIL, $subject, $body, $from);
    }




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
//            exit('<!DOCTYPE html><html><head><title>Incomplete Install</title><meta content="text/html;charset=utf-8" http-equiv="Content-Type"><style type="text/css">*{padding:0;margin:0;}body{background-color:#ebebeb;font-size:12px;font-family:arial,helvetica,sans-serif;color:#666;}#main{margin:200px auto 0;width:960px;}.block{margin-top:15px;border:3px solid #CCC;padding:15px;background-color:#FFF;border-radius:10px;}h1{color:#333;font-weight:bold;font-size:24px;}p{padding:5px 0;}</style></head><body><div id="main"><h1>Incomplete Install</h1><div class="block"><p>It appears the install process did not complete properly. Please re-run the installer and ensure so see it through to the end.</p></div></div></body></html>');
        }
        
    }




    static function MaintCheck() {
        if (file_exists (DOC_ROOT . '/.updates')) exit('<!DOCTYPE html><html><head><title>Maintenance</title><meta content="text/html;charset=utf-8" http-equiv="Content-Type"><style type="text/css">*{padding:0;margin:0;}body{background-color:#ebebeb;font-size:12px;font-family:arial,helvetica,sans-serif;color:#666;}#main{margin:200px auto 0;width:960px;}.block{margin-top:15px;border:3px solid #CCC;padding:15px;background-color:#FFF;border-radius:10px;}h1{color:#333;font-weight:bold;font-size:24px;}p{padding:5px 0;}</style></head><body><div id="main"><h1>Maintenance</h1><div class="block"><p>We are currently undergoing scheduled maintenance. Please try back later.</p><p>Sorry for the inconvenience.</p></div></div></body></html>');
    }

}

?>