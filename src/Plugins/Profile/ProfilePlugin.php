<?php

use AntCMS\AntPlugin;
use AntCMS\AntAuth;

class ProfilePlugin extends AntPlugin
{
    protected $antAuth;

    public function handlePluginRoute(array $route)
    {
        $this->antAuth = new AntAuth;
        $currentStep = $route[0] ?? 'none';
        switch ($currentStep) {
            case 'logout':
                $this->antAuth->invalidateSession();
                if (!$this->antAuth->isAuthenticated()) {
                    echo "You have been logged out.";
                } else {
                    echo "There was an error logging you out.";
                }
                exit;
            default:
                echo 'Unknown route "' . $currentStep . '"';
                exit;
        }
    }

    public function getName(): string
    {
        return 'Profile';
    }
}
