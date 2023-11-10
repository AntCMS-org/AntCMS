<?php

namespace Plugins\Admin;

use AntCMS\AntPlugin;
use AntCMS\AntConfig;
use AntCMS\AntTools;
use AntCMS\AntCMS;
use AntCMS\AntTwig;
use AntCMS\AntAuth;
use AntCMS\AntUsers;

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
