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

            $timeout = $this->config->sessionTimeout * 60;

            // Set session timeout values
            $_SESSION['session-expired'] = (!empty($_SESSION['timeout']) && $_SESSION['timeout'] < time());
            $_SESSION['timeout'] = time() + $timeout;

            // var_dump($_SESSION);
        }

        // Start view layer
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
        $this->view = View::getInstance();
    }
}
