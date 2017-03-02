<?php

class Controller
{
    public $view;

    /**
     * Create new controller instance
     */
    public function __construct()
    {
        $this->config = Registry::get('config');

        // Start session
        if (!headers_sent() && session_id() == '') {
            session_start();

            $timeout = $this->config->sessionTimeout * 60;

            // Set session timeout values
            $_SESSION['session-expired'] = (!empty($_SESSION['timeout']) && $_SESSION['timeout'] < time());
            $_SESSION['timeout'] = time() + $timeout;
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
        $view = View::getInstance();
        $this->view = $view;
    }

    /**
     * Determines if user is authenticated
     *
     * @param boolean $strict Whether to observe session expiration when checking for logged in user
     * @return \User|boolean Returns user if logged in, boolean false otherwise
     */
    protected function isAuth($strict = true)
    {
        $userService = new \UserService();
        $loggedInUser = $userService->loginCheck();
        return ($strict && $_SESSION['session-expired']) ? false : $loggedInUser;
    }

    /**
     * Verifies user has a valid login session, redirects otherwise
     *
     * @param boolean $enforceTimeout Whether to redirect due to expired session
     */
    protected function enforceAuth($enforceTimeout = true)
    {
        if (!$this->isAuth(false)) {
            header("Location: " . BASE_URL . '/login/');
            exit();
        }

        // Verify if user's session has timed out
        if ($enforceTimeout && $_SESSION['session-expired']) {

            // Log user out
            $userService = new \UserService();
            $userService->logout();

            // Redirect to login page with session message
            header("Location: " . BASE_URL . '/login/?session-expired');
            exit();
        }
    }
}