<?php

namespace Plugins\Profile;

use AntCMS\AntPlugin;
use AntCMS\AntConfig;
use AntCMS\AntTools;
use AntCMS\AntCMS;
use AntCMS\AntTwig;
use AntCMS\AntAuth;
use AntCMS\AntUsers;

class Profile extends AntPlugin
{
    protected AntAuth $antAuth;
    protected AntTwig $antTwig;
    protected array $params = [
        'AntCMSTitle' => 'AntCMS Profile Management',
        'AntCMSDescription' => 'AntCMS Profile Management',
        'AntCMSAuthor' => 'AntCMS',
        'AntCMSKeywords' => '',
    ];

    public function __construct()
    {
        $this->antAuth = new AntAuth;
        $this->antTwig = new AntTwig;
    }

    public function renderIndex()
    {
        $this->antAuth->checkAuth();
        $this->params['user'] = AntUsers::getUserPublicalKeys($this->antAuth->getUsername());

        $response = $this->response;
        $response->getBody()->write($this->antTwig->renderWithSubLayout('profile_landing', $this->params));
        return $response;
    }

    public function renderFirstTime()
    {
        if (file_exists(antUsersList)) {
            AntCMS::redirectWithoutRequest('/profile');
        }
        $response = $this->response;
        $response->getBody()->write($this->antTwig->renderWithSubLayout('profile_firstTime', $this->params));
        return $response;
    }

    public function submitfirst()
    {
        $POST = $this->request->getParsedBody();

        if (file_exists(antUsersList)) {
            AntCMS::redirectWithoutRequest('/admin');
        }

        if (isset($POST['username']) && isset($POST['password']) && isset($POST['display-name'])) {
            $data = [
                'username' => $POST['username'],
                'password' => $POST['password'],
                'name' => $POST['display-name'],
            ];
            AntUsers::setupFirstUser($data);
            AntCMS::redirectWithoutRequest('/profile');
        } else {
            AntCMS::redirectWithoutRequest('/profile/firsttime');
        }
    }

    public function save()
    {
        $POST = $this->request->getParsedBody();

        $this->antAuth->checkAuth();
        $data['username'] = $POST['username'] ?? null;
        $data['name'] = $POST['display-name'] ?? null;
        $data['password'] = $POST['password'] ?? null;

        foreach ($data as $key => $value) {
            if (is_null($value)) {
                unset($data[$key]);
            }
        }

        AntUsers::updateUser($this->antAuth->getUsername(), $data);
        AntCMS::redirectWithoutRequest('/profile');
    }

    public function logout()
    {
        $response = $this->response;

        $this->antAuth->invalidateSession();
        if (!$this->antAuth->isAuthenticated()) {
            $response->getBody()->write('You have been logged out.');
        } else {
            $response->getBody()->write('There was an error logging you out.');
        }

        return $response;
    }

    public function edit()
    {
        $this->antAuth->checkAuth();
        $user = AntUsers::getUserPublicalKeys($this->antAuth->getUsername());

        if (!$user) {
            AntCMS::redirectWithoutRequest('/profile');
        }

        $user['username'] = $this->antAuth->getUsername();
        $this->params['user'] = $user;

        $response = $this->response;
        $response->getBody()->write($this->antTwig->renderWithSubLayout('profile_edit', $this->params));
        return $response;
    }

    public function resetpassword()
    {
        $this->antAuth->checkAuth();
        $user = AntUsers::getUserPublicalKeys($this->antAuth->getUsername());

        if (!$user) {
            AntCMS::redirectWithoutRequest('/profile');
        }

        $user['username'] = $this->antAuth->getUsername();
        $this->params['user'] = $user;

        $response = $this->response;
        $response->getBody()->write($this->antTwig->renderWithSubLayout('profile_resetPassword', $this->params));
        return $response;
    }

    public function getName(): string
    {
        return 'Profile';
    }
}
