<?php

class Controller
{
    public $view;

    public function __construct()
    {
        $this->view = Registry::get('view');
    }

    public function dispatch(Route $route)
    {
        $this->view->loadPage();
        include(DOC_ROOT . '/' . $route->location);
    }
}