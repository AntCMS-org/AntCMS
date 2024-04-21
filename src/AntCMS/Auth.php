<?php

namespace AntCMS;

use AntCMS\Config;

class Auth
{
    protected $role;
    protected $username;
    protected $authenticated;

    public function getRole()
    {
        return $this->role;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getName()
    {
        $currentUser = Users::getUser($this->username);
        return $currentUser['name'];
    }

    public function isAuthenticated()
    {
        return $this->authenticated ?? false;
    }

    /**
     * Check if the user is authenticated using the credentials in the config file.
     * If the plain text password in the config file is still present, it will be hashed and the config file will be updated.
     * If the user is not authenticated, it will call AntAuth::requireAuth()
     */
    public function checkAuth(): void
    {
        $username = $_SERVER['PHP_AUTH_USER'] ?? null;
        $password = $_SERVER['PHP_AUTH_PW'] ?? null;

        $currentUser = Users::getUser($username);

        if (empty($currentUser['password'])) {
            $this->requireAuth();
        }

        // If the stored password is not hashed in the config, hash it
        if ($password == $currentUser['password']) {
            Users::updateUser($username, ['password' => $password]);

            // Reload the user info so the next step can pass
            $currentUser = Users::getUser($username);
        }

        // If the credentials are still set valid, but the auth cookie has expired, re-require authentication.
        if (!isset($_COOKIE['auth']) && $_COOKIE['auth'] == 'valid') {
            $this->requireAuth();
        }

        if (password_verify($password, $currentUser['password'])) {
            $this->username = $username;
            $this->role = $currentUser['role'] ?? '';

            return;
        }

        $this->requireAuth();
    }

    /**
     * Send an authentication challenge to the browser, with the realm set to the site title in config.
     */
    private function requireAuth(): void
    {
        setcookie("auth", "valid");

        $siteInfo = Config::currentConfig('siteInfo');
        header('WWW-Authenticate: Basic realm="' . $siteInfo['siteTitle'] . '"');
        http_response_code(401);
        echo 'You must enter a valid username and password to access this page';
        exit;
    }

    public function invalidateSession(): void
    {
        $this->authenticated = false;
        $this->requireAuth();
    }
}
