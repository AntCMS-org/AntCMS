<?php

namespace AntCMS;

use AntCMS\AntConfig;
use AntCMS\AntCMS;

class AntAuth
{
    /**
     * Check if the user is authenticated using the credentials in the config file.
     * If the plain text password in the config file is still present, it will be hashed and the config file will be updated.
     * If the user is not authenticated, it will call AntAuth::requireAuth()
     *
     * @return void
     */
    public static function checkAuth()
    {
        $currentConfig = AntConfig::currentConfig();
        $username = $_SERVER['PHP_AUTH_USER'] ?? null;
        $password = $_SERVER['PHP_AUTH_PW'] ?? null;

        if (empty($currentConfig['admin']['password'])) {
            AntCMS::renderException('401', 401, 'You must set a password in your config.yaml file before you can authenticate within AntCMS.');
        }

        // If the stored password is not hashed in the config, hash it
        if ($password == $currentConfig['admin']['password']) {
            $currentConfig['admin']['password'] = password_hash($currentConfig['admin']['password'], PASSWORD_DEFAULT);
            AntConfig::saveConfig($currentConfig);

            // Reload the config so the next step can pass
            $currentConfig = AntConfig::currentConfig();
        }

        // If the credentials are still set valid, but the auth cookie has expired, re-require authentication.
        if (!isset($_COOKIE['auth'])) {
            AntAuth::requireAuth();
        }

        if ($currentConfig['admin']['username'] == $username && password_verify($password, $currentConfig['admin']['password'])) {
            return;
        }

        AntAuth::requireAuth();
    }

    /**
     * Send an authentication challenge to the browser, with the realm set to the site title in config.
     *
     * @return void
     */
    private static function requireAuth()
    {
        setcookie("auth", "true");

        $title = AntConfig::currentConfig('siteInfo.siteTitle');
        header('WWW-Authenticate: Basic realm="' . $title . '"');
        http_response_code(401);
        echo 'You must enter a valid username and password to access this page';
        exit;
    }
}
