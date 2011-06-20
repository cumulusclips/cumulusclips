<?php

class App {

    /**
     * Manually Throw 404 error page
     * @return void 404 page is output to buffer
     */
    static function Throw404() {

        global $config;
        include (DOC_ROOT . '/cc-core/controllers/system_404.php');
        exit();

//        session_write_close();
//        header ('HTTP/1.0 404 Not Found');
//        $curl = curl_init();
//        curl_setopt ($curl, CURLOPT_URL, HOST . '/cc-core/controllers/server_404.php');
//        curl_setopt ($curl, CURLOPT_COOKIE, 'PHPSESSID=' . session_id());
//        curl_setopt ($curl, CURLOPT_TIMEOUT, 5);
//        curl_setopt ($curl, CURLOPT_FOLLOWLOCATION, true);
//        curl_exec ($curl);
//        curl_close ($curl);
//        exit();
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
        $from = 'From: Admin - TechieVideos.com <' . SITE_EMAIL . '>';
        @mail (MAIN_EMAIL, $subject, $body, $from);
    }
}

?>