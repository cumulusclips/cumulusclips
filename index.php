<?php

// Init application
include_once(dirname(__FILE__) . '/cc-core/config/bootstrap.php');

// Determine requested script
$router = new Router();
$route = $router->getRoute();
App::mobileCheck($route);
Registry::set('route', $route);

// Init view layer
$view = new View();
Registry::set('view', $view);

// Init controller and execute requested script
$controller = new Controller();
Registry::set('controller', $controller);
$controller->dispatch($route);

// Render page
$view->render();