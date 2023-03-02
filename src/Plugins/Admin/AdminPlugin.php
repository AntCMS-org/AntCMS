<?php

use AntCMS\AntCMS;
use AntCMS\AntPlugin;
use AntCMS\AntConfig;
use AntCMS\AntPages;
use AntCMS\AntYaml;
use AntCMS\AntAuth;
use AntCMS\AntTools;
use AntCMS\AntTwig;
use AntCMS\AntUsers;

class AdminPlugin extends AntPlugin
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
     * @return void
     */
    public function handlePluginRoute(array $route)
    {
        $currentStep = $route[0] ?? 'none';

        $this->auth = new AntAuth;
        $this->auth->checkAuth();

        $this->antCMS = new AntCMS;
        $this->AntTwig = new AntTwig();

        array_shift($route);

        switch ($currentStep) {
            case 'config':
                $this->configureAntCMS($route);
                break;

            case 'pages':
                $this->managePages($route);
                break;

            default:
                $params = [
                    'AntCMSTitle' => 'AntCMS Admin Dashboard',
                    'AntCMSDescription' => 'The AntCMS admin dashboard',
                    'AntCMSAuthor' => 'AntCMS',
                    'AntCMSKeywords' => '',
                    'user' => AntUsers::getUserPublicalKeys($this->auth->getUsername()),

                ];
                echo $this->AntTwig->renderWithSubLayout('admin_landing_layout', $params);
                break;
        }
    }

    /**
     * @param array<string> $route 
     * @return never 
     */
    private function configureAntCMS(array $route)
    {
        if ($this->auth->getRole() != 'admin') {
            $this->antCMS->renderException("You are not permitted to visit this page.");
        }

        $currentConfig = AntConfig::currentConfig();
        $currentConfigFile = file_get_contents(antConfigFile);
        $params = array(
            'AntCMSTitle' => 'AntCMS Configuration',
            'AntCMSDescription' => 'The AntCMS configuration screen',
            'AntCMSAuthor' => 'AntCMS',
            'AntCMSKeywords' => '',
        );

        switch ($route[0] ?? 'none') {
            case 'edit':
                $params['AntCMSActionURL'] = '//' . $currentConfig['baseURL'] . 'admin/config/save';
                $params['AntCMSTextAreaContent'] = htmlspecialchars($currentConfigFile);

                echo $this->AntTwig->renderWithSubLayout('textarea_edit_layout', $params);
                break;

            case 'save':
                if (!$_POST['textarea']) {
                    header('Location: //' . $currentConfig['baseURL'] . "admin/config/");
                }

                $yaml = AntYaml::parseYaml($_POST['textarea']);
                if (is_array($yaml)) {
                    AntYaml::saveFile(antConfigFile, $yaml);
                }

                header('Location: //' . $currentConfig['baseURL'] . "admin/config/");
                break;

            default:
                foreach ($currentConfig as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $subkey => $subvalue) {
                            if (is_bool($subvalue)) {
                                $currentConfig[$key][$subkey] = ($subvalue) ? 'true' : 'false';
                            }
                        }
                    } else if (is_bool($value)) {
                        $currentConfig[$key] = ($value) ? 'true' : 'false';
                    }
                }

                $params['currentConfig'] = $currentConfig;
                echo $this->AntTwig->renderWithSubLayout('admin_config_layout', $params);
                break;
        }
        exit;
    }

    /**
     * @param array<string> $route 
     * @return never 
     */
    private function managePages(array $route)
    {
        $pageTemplate = $this->antCMS->getPageLayout();
        $HTMLTemplate = $this->antCMS->getThemeTemplate('markdown_edit_layout');
        $pages = AntPages::getPages();
        $params = array(
            'AntCMSTitle' => 'AntCMS Page Management',
            'AntCMSDescription' => 'The AntCMS page management screen',
            'AntCMSAuthor' => 'AntCMS',
            'AntCMSKeywords' => '',
        );

        switch ($route[0] ?? 'none') {
            case 'regenerate':
                AntPages::generatePages();
                header('Location: //' . AntConfig::currentConfig('baseURL') . "admin/pages/");
                exit;

            case 'edit':
                if (!isset($_POST['newpage'])) {
                    array_shift($route);
                    $pagePath = AntTools::convertFunctionaltoFullpath(implode('/', $route));

                    $page = file_get_contents($pagePath);

                    //Finally, we strip off the antContentPath for compatibility with the save function.
                    $pagePath = str_replace(antContentPath, '', $pagePath);
                } else {
                    $pagePath = '/' . $_POST['newpage'];

                    if (!str_ends_with($pagePath, ".md")) {
                        $pagePath .= '.md';
                    }

                    $pagePath = AntTools::repairFilePath($pagePath);
                    $page = "--AntCMS--\nTitle: New Page Title\nAuthor: Author\nDescription: Description of this page.\nKeywords: Keywords\n--AntCMS--\n";
                }

                $params['AntCMSActionURL'] = '//' . AntConfig::currentConfig('baseURL') . "admin/pages/save/{$pagePath}";
                $params['AntCMSTextAreaContent'] = $page;

                echo $this->AntTwig->renderWithSubLayout('markdown_edit_layout', $params);
                break;

            case 'save':
                array_shift($route);
                $pagePath = AntTools::repairFilePath(antContentPath . '/' . implode('/', $route));

                if (!isset($_POST['textarea'])) {
                    header('Location: //' . AntConfig::currentConfig('baseURL') . "admin/pages/");
                }

                file_put_contents($pagePath, $_POST['textarea']);
                header('Location: //' . AntConfig::currentConfig('baseURL') . "admin/pages/");
                exit;

            case 'create':
                $params['BaseURL'] = AntConfig::currentConfig('baseURL');
                echo $this->AntTwig->renderWithSubLayout('admin_new_page_layout', $params);
                break;

            case 'delete':
                array_shift($route);
                $pagePath = AntTools::convertFunctionaltoFullpath(implode('/', $route));

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

                header('Location: //' . AntConfig::currentConfig('baseURL') . "admin/pages/");
                break;

            case 'togglevisibility':
                array_shift($route);
                $pagePath = AntTools::convertFunctionaltoFullpath(implode('/', $route));

                foreach ($pages as $key => $page) {
                    if ($page['fullPagePath'] == $pagePath) {
                        $pages[$key]['showInNav'] = !$page['showInNav'];
                    }
                }

                AntYaml::saveFile(antPagesList, $pages);
                header('Location: //' . AntConfig::currentConfig('baseURL') . "admin/pages/");
                break;

            default:
                foreach ($pages as $key => $page) {
                    $pages[$key]['editurl'] = '//' . AntTools::repairURL(AntConfig::currentConfig('baseURL') . "/admin/pages/edit/" . $page['functionalPagePath']);
                    $pages[$key]['deleteurl'] = '//' . AntTools::repairURL(AntConfig::currentConfig('baseURL') . "/admin/pages/delete/" . $page['functionalPagePath']);
                    $pages[$key]['togglevisibility'] = '//' . AntTools::repairURL(AntConfig::currentConfig('baseURL') . "/admin/pages/togglevisibility/" . $page['functionalPagePath']);
                    $pages[$key]['isvisable'] = $this->boolToWord($page['showInNav']);
                }
                $params = [
                    'AntCMSTitle' => 'AntCMS Admin Dashboard',
                    'AntCMSDescription' => 'The AntCMS admin dashboard',
                    'AntCMSAuthor' => 'AntCMS',
                    'AntCMSKeywords' => '',
                    'pages' => $pages,
                ];
                echo $this->AntTwig->renderWithSubLayout('admin_manage_pages_layout', $params);
                break;
        }
        exit;
    }

    /** 
     * @return string 
     */
    private function boolToWord(bool $value)
    {
        return $value ? 'true' : 'false';
    }
}
