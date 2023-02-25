<?php

namespace AntCMS;

use AntCMS\AntConfig;

class AntAuth
{
    protected $role;
    protected $username;

    /**
     * Check if the user is authenticated using the credentials in the config file.
     * If the plain text password in the config file is still present, it will be hashed and the config file will be updated.
     * If the user is not authenticated, it will call AntAuth::requireAuth()
     *
     * @return void
     */
    public function checkAuth()
    {
        $username = $_SERVER['PHP_AUTH_USER'] ?? null;
        $password = $_SERVER['PHP_AUTH_PW'] ?? null;

        $currentUser = AntUsers::getUser($username);

        if (is_null($currentUser) || empty($currentUser['password'])) {
            $this->requireAuth();
        }

        // If the stored password is not hashed in the config, hash it
        if ($password == $currentUser['password']) {
            $data = [
                'password' => $password
            ];
            AntUsers::updateUser($username, $data);

            // Reload the user info so the next step can pass
            $currentUser = AntUsers::getUser($username);
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

    public function getRole()
    {
        return $this->role;
    }

    public function getUsername()
    {
        return $this->role;
    }

    public function getName()
    {
        $currentUser = AntUsers::getUser($this->username);
        return $currentUser['name'];
    }

    /**
     * Send an authentication challenge to the browser, with the realm set to the site title in config.
     *
     * @return void
     */
    private function requireAuth()
    {
        setcookie("auth", "valid");

        $title = AntConfig::currentConfig('siteInfo.siteTitle');
        header('WWW-Authenticate: Basic realm="' . $title . '"');
        http_response_code(401);
        echo 'You must enter a valid username and password to access this page';
        exit;
    }

    public static function invalidateSession()
    {
        setcookie("auth", "invalid", time() - 3600);
    }
}
