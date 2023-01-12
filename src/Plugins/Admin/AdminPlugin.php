<?php

use AntCMS\AntCMS;
use AntCMS\AntPlugin;
use AntCMS\AntConfig;
use AntCMS\AntPages;
use AntCMS\AntYaml;
use AntCMS\AntAuth;
use AntCMS\AntTools;
use AntCMS\AntTwig;

class AdminPlugin extends AntPlugin
{
    /**
     * @param array<string> $route 
     * @return void
     */
    public function handlePluginRoute(array $route)
    {
        AntAuth::checkAuth();

        $currentStep = $route[0] ?? 'none';
        $antCMS = new AntCMS;
        $pageTemplate = $antCMS->getPageLayout();
        array_shift($route);

        switch ($currentStep) {
            case 'config':
                $this->configureAntCMS($route);

            case 'pages':
                $this->managePages($route);

            default:
                $antTwig = new AntTwig;
                $params = array(
                    'AntCMSTitle' => 'AntCMS Admin Dashboard',
                    'AntCMSDescription' => 'The AntCMS admin dashboard',
                    'AntCMSAuthor' => 'AntCMS',
                    'AntCMSKeywords' => 'N/A',
                );

                $HTMLTemplate = "<h1>AntCMS Admin Plugin</h1>\n";
                $HTMLTemplate .= "<a href='//" . AntConfig::currentConfig('baseURL') . "plugin/admin/config/'>AntCMS Configuration</a><br>\n";
                $HTMLTemplate .= "<a href='//" . AntConfig::currentConfig('baseURL') . "plugin/admin/pages/'>Page management</a><br>\n";
                $pageTemplate = str_replace('<!--AntCMS-Body-->', $HTMLTemplate, $pageTemplate);
                $pageTemplate = $antTwig->renderWithTiwg($pageTemplate, $params);

                echo $pageTemplate;
                break;
        }
    }

    /** @return string  */
    public function getName()
    {
        return 'Admin';
    }

    /**
     * @param array<string> $route 
     * @return never 
     */
    private function configureAntCMS(array $route)
    {
        $antCMS = new AntCMS;
        $pageTemplate = $antCMS->getPageLayout();
        $HTMLTemplate = $antCMS->getThemeTemplate('textarea_edit_layout');
        $currentConfig = AntConfig::currentConfig();
        $currentConfigFile = file_get_contents(antConfigFile);
        $antTwig = new AntTwig;
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
                        $HTMLTemplate .= "<li>$key:</li>\n";
                        $HTMLTemplate .= "<ul>\n";
                        foreach ($value as $key => $value) {
                            $value = is_bool($value) ? $this->boolToWord($value) : $value;
                            $HTMLTemplate .= "<li>$key: $value</li>\n";
                        }
                        $HTMLTemplate .= "</ul>\n";
                    } else {
                        $value = is_bool($value) ? $this->boolToWord($value) : $value;
                        $HTMLTemplate .= "<li>$key: $value</li>\n";
                    }
                }
                $HTMLTemplate .= "</ul>\n";
        }
        $pageTemplate = str_replace('<!--AntCMS-Body-->', $HTMLTemplate, $pageTemplate);
        $pageTemplate = $antTwig->renderWithTiwg($pageTemplate, $params);

        echo $pageTemplate;
        exit;
    }

    /**
     * @param array<string> $route 
     * @return never 
     */
    private function managePages(array $route)
    {
        $antCMS = new AntCMS;
        $pageTemplate = $antCMS->getPageLayout();
        $HTMLTemplate = $antCMS->getThemeTemplate('markdown_edit_layout');
        $pages = AntPages::getPages();
        $antTwig = new AntTwig;
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
                    $pagePath = implode('/', $route);
                    $page = file_get_contents(antContentPath . '/' . $pagePath);
                } else {
                    $pagePath = '/' . $_POST['newpage'];
                    if (!str_ends_with($pagePath, ".md")) {
                        $pagePath .= '.md';
                    }
                    $page = "--AntCMS--\nTitle: New Page Title\nAuthor: Author\nDescription: Description of this page.\nKeywords: Keywords\n--AntCMS--\n";
                }

                $pagePath = AntTools::repairFilePath($pagePath);

                $HTMLTemplate = str_replace('<!--AntCMS-ActionURL-->', '//' . AntConfig::currentConfig('baseURL') . "plugin/admin/pages/save/$pagePath", $HTMLTemplate);
                $HTMLTemplate = str_replace('<!--AntCMS-TextAreaContent-->', htmlspecialchars($page), $HTMLTemplate);
                break;

            case 'save':
                array_shift($route);
                $pagePath = antContentPath . '/' . implode('/', $route);
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

            default:
                $HTMLTemplate = "<h1>Page Management</h1>\n";
                $HTMLTemplate .= "<a href='//" . AntConfig::currentConfig('baseURL') . "plugin/admin/pages/regenerate'>Click here to regenerate the page list</a><br>\n";
                $HTMLTemplate .= "<a href='//" . AntConfig::currentConfig('baseURL') . "plugin/admin/pages/create'>Click here to create a new page</a><br>\n";
                $HTMLTemplate .= "<ul>\n";
                foreach ($pages as $page) {
                    $HTMLTemplate .= "<li>\n";
                    $HTMLTemplate .= "<h2>" . $page['pageTitle'] . "</h2>\n";
                    $HTMLTemplate .= "<a href='//" . AntConfig::currentConfig('baseURL') . "plugin/admin/pages/edit" . $page['functionalPagePath'] . "'>Edit this page</a><br>\n";
                    $HTMLTemplate .= "<ul>\n";
                    $HTMLTemplate .= "<li>Full page path: " . $page['fullPagePath'] . "</li>\n";
                    $HTMLTemplate .= "<li>Functional page path: " . $page['functionalPagePath'] . "</li>\n";
                    $HTMLTemplate .= "<li>Show in navbar: " . $this->boolToWord($page['showInNav']) . "</li>\n";
                    $HTMLTemplate .= "</ul>\n";
                    $HTMLTemplate .= "</li>\n";
                }
                $HTMLTemplate .= "</ul>\n";
        }

        $pageTemplate = str_replace('<!--AntCMS-Body-->', $HTMLTemplate, $pageTemplate);
        $pageTemplate = $antTwig->renderWithTiwg($pageTemplate, $params);

        echo $pageTemplate;
        exit;
    }

    /**
     * @param bool $value 
     * @return string 
     */
    private function boolToWord(bool $value)
    {
        return boolval($value) ? 'true' : 'false';
    }
}
