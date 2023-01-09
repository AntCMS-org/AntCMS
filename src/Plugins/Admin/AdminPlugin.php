<?php

use AntCMS\AntCMS;
use AntCMS\AntPlugin;
use AntCMS\AntConfig;
use AntCMS\AntPages;
use AntCMS\AntYaml;
use AntCMS\AntAuth;
use AntCMS\AntTools;

class AdminPlugin extends AntPlugin
{
    public function handlePluginRoute(array $route)
    {
        AntAuth::checkAuth();

        $currentStep = $route[0] ?? 'none';
        $antCMS = new AntCMS;
        $pageTemplate = $antCMS->getPageLayout();
        $currentConfig = AntConfig::currentConfig();
        array_shift($route);

        switch ($currentStep) {
            case 'config':
                $this->configureAntCMS($route);
                break;

            case 'pages':
                $this->managePages($route);
                break;

            default:
                $HTMLTemplate = "<h1>AntCMS Admin Plugin</h1>\n";
                $HTMLTemplate .= "<a href='//" . $currentConfig['baseURL'] . "plugin/admin/config/'>AntCMS Configuration</a><br>\n";
                $HTMLTemplate .= "<a href='//" . $currentConfig['baseURL'] . "plugin/admin/pages/'>Page management</a><br>\n";
                $pageTemplate = str_replace('<!--AntCMS-Title-->', 'AntCMS Configuration', $pageTemplate);
                $pageTemplate = str_replace('<!--AntCMS-Body-->', $HTMLTemplate, $pageTemplate);

                echo $pageTemplate;
                break;
        }
    }

    public function getName()
    {
        return 'Admin';
    }

    private function configureAntCMS(array $route)
    {
        $antCMS = new AntCMS;
        $pageTemplate = $antCMS->getPageLayout();
        $HTMLTemplate = $antCMS->getThemeTemplate('textarea_edit_layout');
        $currentConfig = AntConfig::currentConfig();
        $currentConfigFile = file_get_contents(antConfigFile);

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
        $pageTemplate = str_replace('<!--AntCMS-Title-->', 'AntCMS Configuration', $pageTemplate);
        $pageTemplate = str_replace('<!--AntCMS-Body-->', $HTMLTemplate, $pageTemplate);

        echo $pageTemplate;
        exit;
    }

    private function managePages(array $route)
    {
        $antCMS = new AntCMS;
        $pageTemplate = $antCMS->getPageLayout();
        $HTMLTemplate = $antCMS->getThemeTemplate('markdown_edit_layout');
        $pages = AntPages::getPages();
        $currentConfig = AntConfig::currentConfig();

        switch ($route[0] ?? 'none') {
            case 'regenerate':
                AntPages::generatePages();
                header('Location: //' . $currentConfig['baseURL'] . "plugin/admin/pages/");
                exit;

            case 'edit':
                if (!isset($_POST['newpage'])) {
                    array_shift($route);
                    $pagePath = implode('/', $route);
                    $page = file_get_contents(antContentPath . '/' . $pagePath);
                } else {
                    $pagePath = '/' . $_POST['newpage'];
                    if (substr($pagePath, -3) !== ".md") {
                        $pagePath .= '.md';
                    }
                    $page = "--AntCMS--\nTitle: New Page Title\nAuthor: Author\nDescription: Description of this page.\nKeywords: Keywords\n--AntCMS--\n";
                }

                $pagePath = AntTools::repairFilePath($pagePath);

                $HTMLTemplate = str_replace('<!--AntCMS-ActionURL-->', '//' . $currentConfig['baseURL'] . "plugin/admin/pages/save/$pagePath", $HTMLTemplate);
                $HTMLTemplate = str_replace('<!--AntCMS-TextAreaContent-->', htmlspecialchars($page), $HTMLTemplate);
                break;

            case 'save':
                array_shift($route);
                $pagePath = antContentPath . '/' . implode('/', $route);
                if (!isset($_POST['textarea'])) {
                    header('Location: //' . $currentConfig['baseURL'] . "plugin/admin/pages/");
                }
                file_put_contents($pagePath, $_POST['textarea']);
                header('Location: //' . $currentConfig['baseURL'] . "plugin/admin/pages/");
                exit;

            case 'create':
                $HTMLTemplate = "<h1>Page Management</h1>\n";
                $HTMLTemplate .= "<p>Create new page</p>\n";
                $HTMLTemplate .= '<form method="post" action="' . '//' . $currentConfig['baseURL'] . 'plugin/admin/pages/edit">';
                $HTMLTemplate .=
                    '<div style="display:flex; flex-direction: row; justify-content: center; align-items: center">
                <label for="input">URL for new page: ' . $currentConfig['baseURL'] . ' </label> <input type="text" name="newpage" id="input">
                <input type="submit" value="Submit">
                </div></form>';
                break;

            default:
                $HTMLTemplate = "<h1>Page Management</h1>\n";
                $HTMLTemplate .= "<a href='//" . $currentConfig['baseURL'] . "plugin/admin/pages/regenerate'>Click here to regenerate the page list</a><br>\n";
                $HTMLTemplate .= "<a href='//" . $currentConfig['baseURL'] . "plugin/admin/pages/create'>Click here to create a new page</a><br>\n";
                $HTMLTemplate .= "<ul>\n";
                foreach ($pages as $page) {
                    $HTMLTemplate .= "<li>\n";
                    $HTMLTemplate .= "<h2>" . $page['pageTitle'] . "</h2>\n";
                    $HTMLTemplate .= "<a href='//" . $currentConfig['baseURL'] . "plugin/admin/pages/edit" . $page['functionalPagePath'] . "'>Edit this page</a><br>\n";
                    $HTMLTemplate .= "<ul>\n";
                    $HTMLTemplate .= "<li>Full page path: " . $page['fullPagePath'] . "</li>\n";
                    $HTMLTemplate .= "<li>Functional page path: " . $page['functionalPagePath'] . "</li>\n";
                    $HTMLTemplate .= "<li>Show in navbar: " . $this->boolToWord($page['showInNav']) . "</li>\n";
                    $HTMLTemplate .= "</ul>\n";
                    $HTMLTemplate .= "</li>\n";
                }
                $HTMLTemplate .= "</ul>\n";
        }

        $pageTemplate = str_replace('<!--AntCMS-Title-->', 'AntCMS Page Management', $pageTemplate);
        $pageTemplate = str_replace('<!--AntCMS-Body-->', $HTMLTemplate, $pageTemplate);

        echo $pageTemplate;
        exit;
    }

    private function boolToWord($value)
    {
        return boolval($value) ? 'true' : 'false';
    }
}
