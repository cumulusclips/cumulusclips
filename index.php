<?php

include_once(dirname(__FILE__) . '/cc-core/config/bootstrap.php');

$router = new Router();
$route = $router->getRoute();
include(DOC_ROOT . '/' . $route->location);