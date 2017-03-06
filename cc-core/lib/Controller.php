<?php

class Controller
{
    public $view;
    public $config;
    public $authService;

    /**
     * Create new controller instance
     */
    public function __construct()
    {
        $this->config = Registry::get('config');
        $this->authService = new \AuthService();

        // Start session
        if (!headers_sent() && session_id() == '') {
            session_start();
            $this->authService->setTimeoutFlags();
        }
    }

    /**
     * Execute and render the given route
     * @param Route $route Route to be executed
     */
    public function dispatch(Route $route)
    {
        $this->view = new \View();
        $this->view->load($route);

        // Execute controller
        include($route->location);

        // Render view for route
        $this->view->render();
    }
}
