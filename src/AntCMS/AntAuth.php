<?php

namespace AntCMS;

use AntCMS\AntConfig;

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
            die("You must set a password in your config.yaml file before you can authenticate within AntCMS.");
        }

        // If the stored password is not hashed in the config, hash it
        if ($password == $currentConfig['admin']['password']) {
            $currentConfig['admin']['password'] = password_hash($currentConfig['admin']['password'], PASSWORD_DEFAULT);
            AntConfig::saveConfig($currentConfig);

            // Reload the config so the next step can pass
            $currentConfig = AntConfig::currentConfig();
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
        $title = AntConfig::currentConfig('siteInfo.siteTitle');
        header('WWW-Authenticate: Basic realm="' . $title . '"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'You must enter a valid username and password to access this page';
        exit;
    }
}
