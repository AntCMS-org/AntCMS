<?php

namespace Plugins\Admin;

use AntCMS\AntPlugin;
use AntCMS\AntConfig;
use AntCMS\AntTools;
use AntCMS\AntCMS;
use AntCMS\AntTwig;
use AntCMS\AntAuth;
use AntCMS\AntUsers;
use AntCMS\AntYaml;
use AntCMS\AntPages;

class Admin extends AntPlugin
{
    protected AntAuth $antAuth;
    protected AntTwig $antTwig;
    protected array $params = [
        'AntCMSTitle' => 'AntCMS Admin Dashboard',
        'AntCMSDescription' => 'The AntCMS admin dashboard',
        'AntCMSAuthor' => 'AntCMS',
        'AntCMSKeywords' => '',

    ];

    public function __construct()
    {
        $this->antAuth = new AntAuth;
        $this->antTwig = new AntTwig;
        $this->antAuth->checkAuth();
    }

    public function renderIndex()
    {
        $this->params['user'] = AntUsers::getUserPublicalKeys($this->antAuth->getUsername());

        $response = $this->response;
        $response->getBody()->write($this->antTwig->renderWithSubLayout('admin_landing', $this->params));
        return $response;
    }

    public function config()
    {
        $this->params = [
            'AntCMSTitle' => 'AntCMS Configuration',
            'AntCMSDescription' => 'The AntCMS configuration screen',
            'AntCMSAuthor' => 'AntCMS',
            'AntCMSKeywords' => '',
        ];

        $currentConfig = AntConfig::currentConfig();

        foreach ($currentConfig as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subkey => $subvalue) {
                    if (is_bool($subvalue)) {
                        $currentConfig[$key][$subkey] = ($subvalue) ? 'true' : 'false';
                    }
                    if (is_array($subvalue)) {
                        $currentConfig[$key][$subkey] = implode(', ', $subvalue);
                    }
                }
            } else if (is_bool($value)) {
                $currentConfig[$key] = ($value) ? 'true' : 'false';
            }
        }

        $this->params['currentConfig'] = $currentConfig;
        $response = $this->response;
        $response->getBody()->write($this->antTwig->renderWithSubLayout('admin_config', $this->params));
        return $response;
    }

    public function editConfig()
    {
        // TODO: Check if the user is an admin
        $this->params = [
            'AntCMSTitle' => 'AntCMS Configuration',
            'AntCMSDescription' => 'The AntCMS configuration screen',
            'AntCMSAuthor' => 'AntCMS',
            'AntCMSKeywords' => '',
        ];

        $currentConfig = AntConfig::currentConfig();
        $currentConfigFile = file_get_contents(antConfigFile);
        $this->params['AntCMSActionURL'] = '//' . $currentConfig['baseURL'] . 'admin/config/save';
        $this->params['AntCMSTextAreaContent'] = htmlspecialchars($currentConfigFile);

        $response = $this->response;
        $response->getBody()->write($this->antTwig->renderWithSubLayout('textareaEdit', $this->params));
        return $response;
    }

    public function saveConfig()
    {
        // TODO: Check if the user is an admin
        $POST = $this->request->getParsedBody();

        if (!$POST['textarea']) {
            AntCMS::redirectWithoutRequest('/admin/config');
        }

        $yaml = AntYaml::parseYaml($POST['textarea']);
        if (is_array($yaml)) {
            AntYaml::saveFile(antConfigFile, $yaml);
        }

        AntCMS::redirectWithoutRequest('/admin/config');
    }

    public function users()
    {
        // TODO: Check if the user is an admin
        $this->params = [
            'AntCMSTitle' => 'AntCMS User Management',
            'AntCMSDescription' => 'The AntCMS user management screen',
            'AntCMSAuthor' => 'AntCMS',
            'AntCMSKeywords' => '',
        ];

        $users = AntUsers::getUsers();
        foreach ($users as $key => $user) {
            unset($users[$key]['password']);
            $users[$key]['username'] = $key;
        }
        $this->params['users'] = $users;

        $response = $this->response;
        $response->getBody()->write($this->antTwig->renderWithSubLayout('admin_users', $this->params));
        return $response;
    }

    public function addUser()
    {
        // TODO: Check if the user is an admin
        $this->params = [
            'AntCMSTitle' => 'AntCMS User Management',
            'AntCMSDescription' => 'The AntCMS user management screen',
            'AntCMSAuthor' => 'AntCMS',
            'AntCMSKeywords' => '',
        ];

        $response = $this->response;
        $response->getBody()->write($this->antTwig->renderWithSubLayout('admin_userAdd', $this->params));
        return $response;
    }

    public function editUser(array $args)
    {
        // TODO: Check if the user is an admin
        $this->params = [
            'AntCMSTitle' => 'AntCMS User Management',
            'AntCMSDescription' => 'The AntCMS user management screen',
            'AntCMSAuthor' => 'AntCMS',
            'AntCMSKeywords' => '',
        ];

        $user = AntUsers::getUserPublicalKeys($args['name']);

        if (!$user) {
            AntCMS::redirectWithoutRequest('/admin/users');
        }

        $user['username'] = $args['name'];
        $this->params['user'] = $user;

        $response = $this->response;
        $response->getBody()->write($this->antTwig->renderWithSubLayout('admin_userEdit', $this->params));
        return $response;
    }

    public function resetpassword(array $args)
    {
        // TODO: Check if the user is an admin
        $this->params = [
            'AntCMSTitle' => 'AntCMS User Management',
            'AntCMSDescription' => 'The AntCMS user management screen',
            'AntCMSAuthor' => 'AntCMS',
            'AntCMSKeywords' => '',
        ];

        $user = AntUsers::getUserPublicalKeys($args['name']);

        if (!$user) {
            AntCMS::redirectWithoutRequest('/admin/users');
        }

        $user['username'] = $args['name'];
        $this->params['user'] = $user;

        $response = $this->response;
        $response->getBody()->write($this->antTwig->renderWithSubLayout('admin_userResetPassword', $this->params));
        return $response;
    }

    public function saveUser()
    {
        // TODO: Check if the user is an admin
        $POST = $this->request->getParsedBody();
        $this->params = [
            'AntCMSTitle' => 'AntCMS User Management',
            'AntCMSDescription' => 'The AntCMS user management screen',
            'AntCMSAuthor' => 'AntCMS',
            'AntCMSKeywords' => '',
        ];

        $data['username'] = $POST['username'] ?? null;
        $data['name'] = $POST['display-name'] ?? null;
        $data['role'] = $POST['role'] ?? null;
        $data['password'] = $POST['password'] ?? null;

        foreach ($data as $key => $value) {
            if (is_null($value)) {
                unset($data[$key]);
            }
        }

        AntUsers::updateUser($POST['originalusername'], $data);
        AntCMS::redirectWithoutRequest('/admin/users');
    }

    public function saveNewUser()
    {
        // TODO: Check if the user is an admin
        $POST = $this->request->getParsedBody();
        AntUsers::addUser($POST);
        AntCMS::redirectWithoutRequest('/admin/users');
    }

    public function regeneratePages()
    {
        AntPages::generatePages();
        AntCMS::redirectWithoutRequest('/admin/pages');
    }

    public function getName(): string
    {
        return 'Admin';
    }

    /** 
     * @return string 
     */
    private function boolToWord(bool $value)
    {
        return $value ? 'true' : 'false';
    }
}
