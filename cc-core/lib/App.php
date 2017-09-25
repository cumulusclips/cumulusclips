<?php

class App
{
    /**
     * Manually Throw 404 error page
     * @global object $config Site configuration settings
     * @return void 404 page is output to buffer
     */
    public static function throw404()
    {
        $controller = Registry::get('controller');
        $router = new Router();
        $controller->dispatch($router->getStaticRoute('system-404'));
        exit();
    }

    /**
     * Load class from library
     * @param string $class The name of the class to be loaded
     * @return void Includes the requested class into memory if not already loaded
     */
    public static function loadClass($class)
    {
        if (!class_exists($class)) {
            $file = stream_resolve_include_path($class . '.php');
            if ($file) {
                include($file);
            }
        }
    }

    /**
     * Write/Append message to log file
     * @param string $log_file name of the log file to write to
     * @param string $message Message to log
     * @return void Message is written to log file
     */
    public static function log($log_file, $message)
    {
        $message .= "\n";
        $handle = fopen($log_file, 'at');
        fwrite($handle, $message);
        fclose($handle);
    }

    /**
     * Send an email notification to the site admin
     * @param string $subject The subject of the alert email
     * @param string $body The body of the message for the alert email
     * @return void sends an alert email to site admin
     */
    public static function alert($subject, $body)
    {
        $mailer = new Mailer(Registry::get('config'));
        $mailer->subject = $subject;
        $mailer->body = $body;
        $mailer->send(Settings::get('admin_email'));
    }

    /**
     * Check if system is fully installed. If they are viewing "/" and system is
     * not installed they are forwared to the installer. If system is not "fully"
     * installed they are given a "Incomplete Install" message
     */
    public static function installCheck()
    {
        if (!file_exists(DOC_ROOT . '/cc-core/system/config.php') && file_exists(DOC_ROOT . '/cc-install')) {
            $PROTOCOL = (!empty($_SERVER['HTTPS'])) ? 'https://' : 'http://';
            $HOSTNAME = $_SERVER['SERVER_NAME'];
            $PORT = ($_SERVER['SERVER_PORT'] == 80 ? '' : ':' . $_SERVER['SERVER_PORT']);
            $PATH = rtrim(preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']), '/');
            $HOST = $PROTOCOL . $HOSTNAME . $PORT . $PATH;
            header("Location: $HOST/cc-install/");
            exit();
        } else if ((!file_exists(DOC_ROOT . '/cc-core/system/config.php') || file_exists(DOC_ROOT . '/cc-install')) && !isset($_GET['first_run'])) {
            exit('<!DOCTYPE html><html><head><title>Incomplete Install</title><meta content="text/html;charset=utf-8" http-equiv="Content-Type"><style type="text/css">*{padding:0;margin:0;}body{background-color:#ebebeb;font-size:12px;font-family:arial,helvetica,sans-serif;color:#666;}#main{margin:200px auto 0;width:960px;}.block{margin-top:15px;border:3px solid #CCC;padding:15px;background-color:#FFF;border-radius:10px;}h1{color:#333;font-weight:bold;font-size:24px;}p{padding:5px 0;}</style></head><body><div id="main"><h1>Incomplete Install</h1><div class="block"><p>It appears the install process did not complete properly. Please re-run the installer and ensure so see it through to the end.</p></div></div></body></html>');
        }
    }

    /**
     * Check if a system update is in progress and display a "Maintenance" message
     * to any user attempting to access the site
     */
    public static function maintCheck()
    {
        if (file_exists(DOC_ROOT . '/.updates')) exit('<!DOCTYPE html><html><head><title>Maintenance</title><meta content="text/html;charset=utf-8" http-equiv="Content-Type"><style type="text/css">*{padding:0;margin:0;}body{background-color:#ebebeb;font-size:12px;font-family:arial,helvetica,sans-serif;color:#666;}#main{margin:200px auto 0;width:960px;}.block{margin-top:15px;border:3px solid #CCC;padding:15px;background-color:#FFF;border-radius:10px;}h1{color:#333;font-weight:bold;font-size:24px;}p{padding:5px 0;}</style></head><body><div id="main"><h1>Maintenance</h1><div class="block"><p>We are currently undergoing scheduled maintenance. Please try back later.</p><p>Sorry for the inconvenience.</p></div></div></body></html>');
    }

    /**
     * Check if uploads are enabled and display message if applicable
     * @global object $config
     * @return void Script is terminated, user is presented with uploads disabled message
     */
    public static function enableUploadsCheck()
    {
        $config = Registry::get('config');
        if ($config->enableUploads != '1') exit('<!DOCTYPE html><html><head><title>Uploads Disabled</title><meta content="text/html;charset=utf-8" http-equiv="Content-Type"><style type="text/css">*{padding:0;margin:0;}body{background-color:#ebebeb;font-size:12px;font-family:arial,helvetica,sans-serif;color:#666;}#main{margin:200px auto 0;width:960px;}.block{margin-top:15px;border:3px solid #CCC;padding:15px;background-color:#FFF;border-radius:10px;}h1{color:#333;font-weight:bold;font-size:24px;}p{padding:5px 0;}</style></head><body><div id="main"><h1>Uploads Disabled</h1><div class="block"><p>Your server does not meet the minimum requirements for video encoding. As a result video uploads have been disabled. Please check with your web host to ensure they fully support CumulusClips.</p><p>Visit the Admin Panel -> Settings -> Video, to re-check your system and enable uploads. You could also use a plugin to manage video encoding for you.</p></div></div></body></html>');
    }

    /**
     * Check for mobile devices or mobile site opt-out. If a mobile device is
     * detected via it's user agent header they will be sent to the mobile version
     * of the site unless they're already viewing the mobile site or have opted
     * out from it.
     * @param Route $route Current route requested
     */
    public static function mobileCheck(Route $route)
    {
        // Verify if user opted-out from Mobile site
        if (isset($_GET['nomobile'])) {
            setcookie('nomobile', md5('nomobile'), time()+3600*12);
        }

        // Redirect to mobile if user hasn't opted out from mobile site
        $agent = '/ip(ad|hone|od)|android|Windows Phone/i';
        if (
            (boolean) Settings::get('mobile_site')
            && isset($_SERVER['HTTP_USER_AGENT'])
            && preg_match($agent, $_SERVER['HTTP_USER_AGENT'])
            && !isset($_COOKIE['nomobile'])
            && !isset($_GET['nomobile'])
            && !in_array($route->type, array(Route::MOBILE, Route::AGNOSTIC))
        ) {
            // Detect if route is for video or private play page and redirect to mobile version instead
            $router = new Router();
            $playRoute = $router->getStaticRoute('play');
            $privateRoute = $router->getStaticRoute('play-private');
            $requestPath = trim($router->getRequestUri(), '/');
            if ($playRoute->path == $route->path) {
                preg_match('#^' . $route->path . '$#i', $requestPath, $matches);
                header("Location: " . MOBILE_HOST . '/v/' . $matches[1] . '/');
                exit();
            } else if ($privateRoute->path == $route->path) {
                preg_match('#^' . $route->path . '$#i', $requestPath, $matches);
                header("Location: " . MOBILE_HOST . '/p/' . $matches[1] . '/');
                exit();
            }

            // Redirect to mobile homepage
            header("Location: " . MOBILE_HOST . "/");
            exit();
        }
    }

    /**
     * Determines whether file is a valid upload file
     *
     * @param string $file Absolute path to uploaded file
     * @param \User $user User the uploaded file should belong to
     * @param string $uploadType Type of upload the file is supposed to be
     * @return boolean Returns true if file is valid upload file, false otherwise
     */
    public static function isValidUpload($file, \User $user, $uploadType)
    {
        // Verify file exists
        if (!file_exists($file)) {
            return false;
        }

        if (!in_array($uploadType, array('video', 'image', 'library', 'addon'))) {
            throw new Exception('Invalid upload type');
        }

        // Determine if user is allowed to upload addons
        $userService = new \UserService();
        if ($uploadType === 'addon' && !$userService->checkPermissions('manage_settings', $user)) {
            return false;
        }

        // Verify file has a valid upload filename
        $path = preg_quote(UPLOAD_PATH . '/temp/', '/');
        $regex = '/^' . $path . '([0-9]+)\-' . $uploadType . '\-[0-9]+/';
        if (!preg_match($regex, $file, $matches)) {
            return false;
        }

        // Verify file was uploaded by user
        if ((int) $matches[1] !== $user->userId) {
            return false;
        }

        return true;
    }
}