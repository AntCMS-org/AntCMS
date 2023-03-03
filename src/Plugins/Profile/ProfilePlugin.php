<?php

use AntCMS\AntPlugin;
use AntCMS\AntAuth;
use AntCMS\AntCMS;
use AntCMS\AntTwig;
use AntCMS\AntUsers;

class ProfilePlugin extends AntPlugin
{
    protected $antAuth;
    protected $antTwig;


    public function handlePluginRoute(array $route)
    {
        $this->antAuth = new AntAuth;
        $this->antTwig = new AntTwig;
        $currentStep = $route[0] ?? 'none';

        $params = [
            'AntCMSTitle' => 'AntCMS Profile Management',
            'AntCMSDescription' => 'AntCMS Profile Management',
            'AntCMSAuthor' => 'AntCMS',
            'AntCMSKeywords' => '',
        ];

        switch ($currentStep) {
            case 'firsttime':
                if (file_exists(antUsersList)) {
                    AntCMS::redirect('/admin');
                }
                echo $this->antTwig->renderWithSubLayout('profile_firsttime_layout', $params);
                break;

            case 'submitfirst':
                if (file_exists(antUsersList)) {
                    AntCMS::redirect('/admin');
                }

                if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['display-name'])) {
                    $data = [
                        'username' => $_POST['username'],
                        'password' => $_POST['password'],
                        'name' => $_POST['display-name'],
                    ];
                    AntUsers::setupFirstUser($data);
                    AntCMS::redirect('/admin');
                } else {
                    AntCMS::redirect('/profile/firsttime');
                }
                break;

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
        }
        exit;
    }

    public function getName(): string
    {
        return 'Profile';
    }
}
