<?php

use AntCMS\AntCMS;
use AntCMS\Plugin;
use AntCMS\Config;
use AntCMS\Pages;
use AntCMS\AntYaml;
use AntCMS\Auth;
use AntCMS\Tools;
use AntCMS\Twig;
use AntCMS\Users;

class AdminPlugin extends Plugin
{
    protected $auth;
    protected $antCMS;
    protected $AntTwig;

    public function getName(): string
    {
        return 'Admin';
    }

    /**
     * @param array<string> $route
     */
    public function handlePluginRoute(array $route): void
    {
        $currentStep = $route[0] ?? 'none';

        $this->auth = new Auth();
        $this->auth->checkAuth();

        $this->antCMS = new AntCMS();
        $this->AntTwig = new Twig();

        array_shift($route);

        switch ($currentStep) {
            case 'config':
                $this->configureAntCMS($route);

                // no break
            case 'pages':
                $this->managePages($route);

                // no break
            case 'users':
                $this->userManagement($route);

                // no break
            default:
                $params = [
                    'AntCMSTitle' => 'AntCMS Admin Dashboard',
                    'AntCMSDescription' => 'The AntCMS admin dashboard',
                    'AntCMSAuthor' => 'AntCMS',
                    'AntCMSKeywords' => '',
                    'user' => Users::getUserPublicalKeys($this->auth->getUsername()),

                ];
                echo $this->AntTwig->renderWithSubLayout('admin_landing', $params);
                break;
        }
    }

    /**
     * @param array<string> $route
     * @return never
     */
    private function configureAntCMS(array $route): void
    {
        if ($this->auth->getRole() != 'admin') {
            $this->antCMS->renderException("You are not permitted to visit this page.");
        }

        $currentConfig = Config::currentConfig();
        $currentConfigFile = file_get_contents(antConfigFile);
        $params = ['AntCMSTitle' => 'AntCMS Configuration', 'AntCMSDescription' => 'The AntCMS configuration screen', 'AntCMSAuthor' => 'AntCMS', 'AntCMSKeywords' => ''];

        switch ($route[0] ?? 'none') {
            case 'edit':
                $params['AntCMSActionURL'] = '//' . $currentConfig['baseURL'] . 'admin/config/save';
                $params['AntCMSTextAreaContent'] = htmlspecialchars($currentConfigFile);

                echo $this->AntTwig->renderWithSubLayout('textareaEdit', $params);
                break;

            case 'save':
                if (!$_POST['textarea']) {
                    Flight::redirect('/admin/config');
                    exit;
                }

                try {
                    $yaml = AntYaml::parseYaml($_POST['textarea']);
                    if (is_array($yaml)) {
                        AntYaml::saveFile(antConfigFile, $yaml);
                    }
                } catch (Exception) {
                }

                Flight::redirect('/admin/config');
                exit;

            default:
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
                    } elseif (is_bool($value)) {
                        $currentConfig[$key] = ($value) ? 'true' : 'false';
                    }
                }

                $params['currentConfig'] = $currentConfig;
                echo $this->AntTwig->renderWithSubLayout('admin_config', $params);
                break;
        }
        exit;
    }

    /**
     * @param array<string> $route
     * @return never
     */
    private function managePages(array $route): void
    {
        $pages = Pages::getPages();
        $params = ['AntCMSTitle' => 'AntCMS Page Management', 'AntCMSDescription' => 'The AntCMS page management screen', 'AntCMSAuthor' => 'AntCMS', 'AntCMSKeywords' => ''];

        switch ($route[0] ?? 'none') {
            case 'regenerate':
                Pages::generatePages();
                Flight::redirect('/admin/pages');
                exit;

            case 'edit':
                if (!isset($_POST['newpage'])) {
                    array_shift($route);
                    $pagePath = Tools::convertFunctionaltoFullpath(implode('/', $route));

                    $page = file_get_contents($pagePath);

                    //Finally, we strip off the antContentPath for compatibility with the save function.
                    $pagePath = str_replace(antContentPath, '', $pagePath);
                } else {
                    $pagePath = '/' . $_POST['newpage'];

                    if (!str_ends_with($pagePath, ".md")) {
                        $pagePath .= '.md';
                    }

                    $pagePath = Tools::repairFilePath($pagePath);
                    $name = $this->auth->getName();
                    $page = "--AntCMS--\nTitle: New Page Title\nAuthor: $name\nDescription: Description of this page.\nKeywords: Keywords\n--AntCMS--\n";
                }

                $params['AntCMSActionURL'] = '//' . Config::currentConfig('baseURL') . "admin/pages/save/{$pagePath}";
                $params['AntCMSTextAreaContent'] = $page;

                echo $this->AntTwig->renderWithSubLayout('markdownEdit', $params);
                break;

            case 'save':
                array_shift($route);
                $pagePath = Tools::repairFilePath(antContentPath . '/' . implode('/', $route));

                if (!isset($_POST['textarea'])) {
                    Flight::redirect('/admin/pages');
                    exit;
                }

                file_put_contents($pagePath, $_POST['textarea']);
                Flight::redirect('/admin/pages');
                exit;

            case 'create':
                $params['BaseURL'] = Config::currentConfig('baseURL');
                echo $this->AntTwig->renderWithSubLayout('admin_newPage', $params);
                break;

            case 'delete':
                array_shift($route);
                $pagePath = Tools::convertFunctionaltoFullpath(implode('/', $route));

                // Find the key associated with the functional page path, then remove it from our temp pages array
                foreach ($pages as $key => $page) {
                    if ($page['fullPagePath'] == $pagePath) {
                        unset($pages[$key]);
                    }
                }

                // If we were able to delete the page, update the pages list with the updated pages array.
                if (file_exists($pagePath) && unlink($pagePath)) {
                    AntYaml::saveFile(antPagesList, $pages);
                }

                Flight::redirect('/admin/pages');
                exit;

            case 'togglevisibility':
                array_shift($route);
                $pagePath = Tools::convertFunctionaltoFullpath(implode('/', $route));

                foreach ($pages as $key => $page) {
                    if ($page['fullPagePath'] == $pagePath) {
                        $pages[$key]['showInNav'] = !$page['showInNav'];
                    }
                }

                AntYaml::saveFile(antPagesList, $pages);
                Flight::redirect('/admin/pages');
                exit;

            default:
                foreach ($pages as $key => $page) {
                    $pages[$key]['editurl'] = '//' . Tools::repairURL(Config::currentConfig('baseURL') . "/admin/pages/edit/" . $page['functionalPagePath']);
                    $pages[$key]['deleteurl'] = '//' . Tools::repairURL(Config::currentConfig('baseURL') . "/admin/pages/delete/" . $page['functionalPagePath']);
                    $pages[$key]['togglevisibility'] = '//' . Tools::repairURL(Config::currentConfig('baseURL') . "/admin/pages/togglevisibility/" . $page['functionalPagePath']);
                    $pages[$key]['isvisable'] = $this->boolToWord($page['showInNav']);
                }
                $params = [
                    'AntCMSTitle' => 'AntCMS Admin Dashboard',
                    'AntCMSDescription' => 'The AntCMS admin dashboard',
                    'AntCMSAuthor' => 'AntCMS',
                    'AntCMSKeywords' => '',
                    'pages' => $pages,
                ];
                echo $this->AntTwig->renderWithSubLayout('admin_managePages', $params);
                break;
        }
        exit;
    }

    private function userManagement(array $route): void
    {
        if ($this->auth->getRole() != 'admin') {
            $this->antCMS->renderException("You are not permitted to visit this page.");
        }

        $params = ['AntCMSTitle' => 'AntCMS User Management', 'AntCMSDescription' => 'The AntCMS user management screen', 'AntCMSAuthor' => 'AntCMS', 'AntCMSKeywords' => ''];

        switch ($route[0] ?? 'none') {
            case 'add':
                echo $this->AntTwig->renderWithSubLayout('admin_userAdd', $params);
                break;

            case 'edit':
                $user = Users::getUserPublicalKeys($route[1]);

                if (!$user) {
                    Flight::redirect('/admin/users');
                    exit;
                }

                $user['username'] = $route[1];
                $params['user'] = $user;

                echo $this->AntTwig->renderWithSubLayout('admin_userEdit', $params);
                break;

            case 'resetpassword':
                $user = Users::getUserPublicalKeys($route[1]);

                if (!$user) {
                    Flight::redirect('/admin/users');
                    exit;
                }

                $user['username'] = $route[1];
                $params['user'] = $user;

                echo $this->AntTwig->renderWithSubLayout('admin_userResetPassword', $params);
                break;

            case 'save':
                $data['username'] = $_POST['username'] ?? null;
                $data['name'] = $_POST['display-name'] ?? null;
                $data['role'] = $_POST['role'] ?? null;
                $data['password'] = $_POST['password'] ?? null;

                foreach ($data as $key => $value) {
                    if (is_null($value)) {
                        unset($data[$key]);
                    }
                }

                Users::updateUser($_POST['originalusername'], $data);
                Flight::redirect('/admin/users');
                exit;
            case 'savenew':
                Users::addUser($_POST);
                Flight::redirect('/admin/users');
                exit;

            default:
                $users = Users::getUsers();
                foreach ($users as $key => $user) {
                    unset($users[$key]['password']);
                    $users[$key]['username'] = $key;
                }
                $params['users'] = $users;
                echo $this->AntTwig->renderWithSubLayout('admin_users', $params);
                break;
        }
        exit;
    }

    private function boolToWord(bool $value): string
    {
        return $value ? 'true' : 'false';
    }
}
