<?php

class Controller
{
    public $view;

    /**
     * Create new controller instance 
     */
    public function __construct()
    {
        $this->_initView();
    }

    /**
     * Execute and render the given route
     * @param Route $route Route to be executed
     */
    public function dispatch(Route $route)
    {
        $this->view->load($route);
        include($route->location);
        $this->view->render();
    }
    
    /**
     * Setup view instance for use by controller
     */
    protected function _initView()
    {
        $view = View::getInstance();
        $this->view = $view;
    }
}