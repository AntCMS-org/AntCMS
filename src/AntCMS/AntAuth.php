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

        if (empty($currentConfig['admin']['password'])) {
            die("You must set a password in your config.yaml file before you can authenticate within AntCMS.");
        }

        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
            //First, we check if the passwords match in plain text. If it does, we hash the password and update the config file
            if ($_SERVER['PHP_AUTH_PW'] == $currentConfig['admin']['password']) {
                $currentConfig['admin']['password'] = password_hash($currentConfig['admin']['password'], PASSWORD_DEFAULT);
                AntConfig::saveConfig($currentConfig);
            }

            //Now, we can perform the check as normal
            if ($currentConfig['admin']['username'] == $_SERVER['PHP_AUTH_USER'] && password_verify($_SERVER['PHP_AUTH_PW'], $currentConfig['admin']['password'])) {
                return;
            }
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
        $currentConfig = AntConfig::currentConfig();
        header('WWW-Authenticate: Basic realm="' . $currentConfig['SiteInfo']['siteTitle'] . '"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'You must enter a valid username and password to access this page';
        exit;
    }
}
