<?php

// Init application
include_once(dirname(__FILE__) . '/cc-core/system/bootstrap.php');

// Determine requested script
$router = new Router();
$route = $router->getRoute();
App::mobileCheck($route);
Registry::set('route', $route);

// Init controller and execute requested script
$controller = new Controller();
Registry::set('controller', $controller);
$controller->dispatch($route);