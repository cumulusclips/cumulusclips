<?php

class AuthService extends ServiceAbstract
{
    /**
     * @var int Session timeout in minutes
     */
    protected $sessionTimeout;

    /**
     * Instantiates auth service
     */
    public function __construct()
    {
        $config = Registry::get('config');
        $this->sessionTimeout = $config->sessionTimeout;
    }

    /**
     * Sets flags that state whether an auth session is valid or expired
     */
    public function setTimeoutFlags()
    {
        if (isset($_SESSION['auth'])) {
            $_SESSION['auth']->sessionExpired = $_SESSION['auth']->timeout < time();
            $_SESSION['auth']->timeout = time() + ($this->sessionTimeout * 60);
        }
    }

    /**
     * Determines if user is authenticated
     *
     * @param boolean $strict Whether to observe session expiration when checking for logged in user
     * @return \User|boolean Returns user if logged in, boolean false otherwise
     */
    public function getAuthUser()
    {
        $userMapper = new \UserMapper();
        if (isset($_SESSION['auth'])) {
            return $userMapper->getUserById($_SESSION['auth']->userId);
        } else {
            return false;
        }
    }

    /**
     * Validates given credentials and provides associated user
     *
     * @param string $username Username of user to validate
     * @param string $password Password of user to validate
     * @return \User|boolean Returns user if credentials are valid, boolean false otherwise
     */
    public function validateCredentials($username, $password)
    {
        $userMapper = new \UserMapper();
        return $userMapper->getUserByCustom(array(
            'username' => $username,
            'password' => md5($password),
            'status' => 'active'
        ));
    }

    /**
     * Login a user
     *
     * @param \User $user User to log in
     */
    public function login(\User $user)
    {
        $_SESSION['auth'] = (object) array(
            'userId' => $user->userId,
            'sessionExpired' => false,
            'timeout' => time() + ($this->sessionTimeout * 60)
        );
    }

    /**
     * Log a user out
     */
    public function logout()
    {
        unset($_SESSION['auth']);
    }

    /**
     * Verifies if user is logged in, redirects otherwise
     */
    public function enforceAuth()
    {
        if (!isset($_SESSION['auth'])) {
            header("Location: " . BASE_URL . '/login/');
            exit();
        }
    }

    /**
     * Verifies is user's session is expired, and logs them out if not
     *
     * @param boolean $redirectOnTimeout Whether to redirect user in case of expired session
     */
    public function enforceTimeout($redirectOnTimeout = false)
    {
        if (!empty($_SESSION['auth']->sessionExpired)) {

            // Log user out
            $this->logout();

            // Redirect to login page with session message
            if ($redirectOnTimeout) {
                header("Location: " . BASE_URL . '/login/?session-expired');
                exit();
            }
        }
    }
}
