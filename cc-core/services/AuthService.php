<?php

class AuthService extends ServiceAbstract
{
    /**
     * Determines if user is authenticated
     *
     * @param boolean $strict Whether to observe session expiration when checking for logged in user
     * @return \User|boolean Returns user if logged in, boolean false otherwise
     */
    public function getAuthUser()
    {
        $userMapper = new \UserMapper();
        if (isset($_SESSION['loggedInUserId'])) {
            return $userMapper->getUserById($_SESSION['loggedInUserId']);
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
        $_SESSION['loggedInUserId'] = $user->userId;
    }

    /**
     * Log a user out
     */
    public function logout()
    {
        unset($_SESSION['loggedInUserId']);
    }

    /**
     * Sets session timeout values
     */
    public function setTimeoutValues()
    {
        $config = Registry::get('config');
        $_SESSION['session-expired'] = (!empty($_SESSION['timeout']) && $_SESSION['timeout'] < time());
        $_SESSION['timeout'] = time() + ($config->sessionTimeout * 60);
    }

    /**
     * Verifies if user is logged in, redirects otherwise
     */
    public function enforceAuth()
    {
        if (!isset($_SESSION['loggedInUserId'])) {
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
        if ($_SESSION['session-expired']) {

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
