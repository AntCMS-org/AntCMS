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
        $this->auth = new AntAuth;
        $this->auth->checkAuth();

        $currentStep = $route[0] ?? 'none';
        $this->antCMS = new AntCMS;
        $pageTemplate = $this->antCMS->getPageLayout();
        array_shift($route);

        switch ($currentStep) {
            case 'config':
                $this->configureAntCMS($route);

            case 'pages':
                $this->managePages($route);

            default:
                $HTMLTemplate = $this->antCMS->getThemeTemplate('admin_landing_layout');
                $params = ['user' => AntUsers::getUserPublicalKeys($this->auth->getUsername())];

                $HTMLTemplate = AntTwig::renderWithTiwg($HTMLTemplate, $params);
                $params = [
                    'AntCMSTitle' => 'AntCMS Admin Dashboard',
                    'AntCMSDescription' => 'The AntCMS admin dashboard',
                    'AntCMSAuthor' => 'AntCMS',
                    'AntCMSKeywords' => '',
                    'AntCMSBody' => $HTMLTemplate,

                ];
                echo AntTwig::renderWithTiwg($pageTemplate, $params);
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
            AntCMS::renderException("You are not permitted to visit this page.");
        }

        $pageTemplate = $this->antCMS->getPageLayout();
        $HTMLTemplate = $this->antCMS->getThemeTemplate('textarea_edit_layout');
        $currentConfig = AntConfig::currentConfig();
        $currentConfigFile = file_get_contents(antConfigFile);
        $params = array(
            'AntCMSTitle' => 'AntCMS Configuration',
            'AntCMSDescription' => 'The AntCMS configuration screen',
            'AntCMSAuthor' => 'AntCMS',
            'AntCMSKeywords' => 'N/A',
        );

        switch ($route[0] ?? 'none') {
            case 'edit':
                $HTMLTemplate = str_replace('<!--AntCMS-ActionURL-->', '//' . $currentConfig['baseURL'] . 'plugin/admin/config/save', $HTMLTemplate);
                $HTMLTemplate = str_replace('<!--AntCMS-TextAreaContent-->', htmlspecialchars($currentConfigFile), $HTMLTemplate);
                break;

            case 'save':
                if (!$_POST['textarea']) {
                    header('Location: //' . $currentConfig['baseURL'] . "plugin/admin/config/");
                }

                $yaml = AntYaml::parseYaml($_POST['textarea']);
                if (is_array($yaml)) {
                    AntYaml::saveFile(antConfigFile, $yaml);
                }

                header('Location: //' . $currentConfig['baseURL'] . "plugin/admin/config/");
                exit;

            default:
                $HTMLTemplate = "<h1>AntCMS Configuration</h1>\n";
                $HTMLTemplate .= "<a href='//" . $currentConfig['baseURL'] . "plugin/admin/config/edit'>Click here to edit the config file</a><br>\n";
                $HTMLTemplate .= "<ul>\n";
                foreach ($currentConfig as $key => $value) {
                    if (is_array($value)) {
                        $HTMLTemplate .= "<li>{$key}:</li>\n";
                        $HTMLTemplate .= "<ul>\n";
                        foreach ($value as $key => $value) {
                            $value = is_bool($value) ? $this->boolToWord($value) : $value;
                            $HTMLTemplate .= "<li>{$key}: {$value}</li>\n";
                        }

                        $HTMLTemplate .= "</ul>\n";
                    } else {
                        $value = is_bool($value) ? $this->boolToWord($value) : $value;
                        $HTMLTemplate .= "<li>{$key}: {$value}</li>\n";
                    }
                }

                $HTMLTemplate .= "</ul>\n";
        }

        $params['AntCMSBody'] = $HTMLTemplate;
        echo AntTwig::renderWithTiwg($pageTemplate, $params);
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
            'AntCMSKeywords' => 'N/A',
        );

        switch ($route[0] ?? 'none') {
            case 'regenerate':
                AntPages::generatePages();
                header('Location: //' . AntConfig::currentConfig('baseURL') . "plugin/admin/pages/");
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

                $HTMLTemplate = str_replace('<!--AntCMS-ActionURL-->', '//' . AntConfig::currentConfig('baseURL') . "plugin/admin/pages/save/{$pagePath}", $HTMLTemplate);
                $HTMLTemplate = str_replace('<!--AntCMS-TextAreaContent-->', htmlspecialchars($page), $HTMLTemplate);
                break;

            case 'save':
                array_shift($route);
                $pagePath = AntTools::repairFilePath(antContentPath . '/' . implode('/', $route));

                if (!isset($_POST['textarea'])) {
                    header('Location: //' . AntConfig::currentConfig('baseURL') . "plugin/admin/pages/");
                }

                file_put_contents($pagePath, $_POST['textarea']);
                header('Location: //' . AntConfig::currentConfig('baseURL') . "plugin/admin/pages/");
                exit;

            case 'create':
                $HTMLTemplate = "<h1>Page Management</h1>\n";
                $HTMLTemplate .= "<p>Create new page</p>\n";
                $HTMLTemplate .= '<form method="post" action="' . '//' . AntConfig::currentConfig('baseURL') . 'plugin/admin/pages/edit">';
                $HTMLTemplate .=
                    '<div style="display:flex; flex-direction: row; justify-content: center; align-items: center">
                <label for="input">URL for new page: ' . AntConfig::currentConfig('baseURL') . ' </label> <input type="text" name="newpage" id="input">
                <input type="submit" value="Submit">
                </div></form>';
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

                header('Location: //' . AntConfig::currentConfig('baseURL') . "plugin/admin/pages/");
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
                header('Location: //' . AntConfig::currentConfig('baseURL') . "plugin/admin/pages/");
                break;

            default:
                $HTMLTemplate = $this->antCMS->getThemeTemplate('admin_manage_pages_layout');
                foreach ($pages as $key => $page) {
                    $pages[$key]['editurl'] = '//' . AntTools::repairURL(AntConfig::currentConfig('baseURL') . "/plugin/admin/pages/edit/" . $page['functionalPagePath']);
                    $pages[$key]['deleteurl'] = '//' . AntTools::repairURL(AntConfig::currentConfig('baseURL') . "/plugin/admin/pages/delete/" . $page['functionalPagePath']);
                    $pages[$key]['togglevisibility'] = '//' . AntTools::repairURL(AntConfig::currentConfig('baseURL') . "/plugin/admin/pages/togglevisibility/" . $page['functionalPagePath']);
                    $pages[$key]['isvisable'] = $this->boolToWord($page['showInNav']);
                }
                $params['pages'] = $pages;
                $HTMLTemplate = AntTwig::renderWithTiwg($HTMLTemplate, $params);
        }

        $params['AntCMSBody'] = $HTMLTemplate;
        echo AntTwig::renderWithTiwg($pageTemplate, $params);
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
