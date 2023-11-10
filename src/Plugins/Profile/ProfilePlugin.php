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
                    AntCMS::redirectWithoutRequest('/admin');
                }
                echo $this->antTwig->renderWithSubLayout('profile_firstTime', $params);
                break;

            case 'submitfirst':
                if (file_exists(antUsersList)) {
                    AntCMS::redirectWithoutRequest('/admin');
                }

                if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['display-name'])) {
                    $data = [
                        'username' => $_POST['username'],
                        'password' => $_POST['password'],
                        'name' => $_POST['display-name'],
                    ];
                    AntUsers::setupFirstUser($data);
                    AntCMS::redirectWithoutRequest('/admin');
                } else {
                    AntCMS::redirectWithoutRequest('/profile/firsttime');
                }
                break;

            case 'edit':
                $this->antAuth->checkAuth();
                $user = AntUsers::getUserPublicalKeys($this->antAuth->getUsername());

                if (!$user) {
                    AntCMS::redirectWithoutRequest('/profile');
                }

                $user['username'] = $this->antAuth->getUsername();
                $params['user'] = $user;

                echo $this->antTwig->renderWithSubLayout('profile_edit', $params);
                break;

            case 'resetpassword':
                $this->antAuth->checkAuth();
                $user = AntUsers::getUserPublicalKeys($this->antAuth->getUsername());

                if (!$user) {
                    AntCMS::redirectWithoutRequest('/profile');
                }

                $user['username'] = $this->antAuth->getUsername();
                $params['user'] = $user;

                echo $this->antTwig->renderWithSubLayout('profile_resetPassword', $params);
                break;

            case 'save':
                $this->antAuth->checkAuth();
                $data['username'] = $_POST['username'] ?? null;
                $data['name'] = $_POST['display-name'] ?? null;
                $data['password'] = $_POST['password'] ?? null;

                foreach ($data as $key => $value) {
                    if (is_null($value)) {
                        unset($data[$key]);
                    }
                }

                AntUsers::updateUser($this->antAuth->getUsername(), $data);
                AntCMS::redirectWithoutRequest('/profile');
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
                $this->antAuth->checkAuth();
                $params['user'] =  AntUsers::getUserPublicalKeys($this->antAuth->getUsername());
                echo $this->antTwig->renderWithSubLayout('profile_landing', $params);
        }
        exit;
    }

    public function getName(): string
    {
        return 'Profile';
    }
}
