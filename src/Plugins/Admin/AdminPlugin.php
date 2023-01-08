<?php

use AntCMS\AntCMS;
use AntCMS\AntPlugin;
use AntCMS\AntConfig;
use AntCMS\AntPages;
use AntCMS\AntYaml;

class AdminPlugin extends AntPlugin
{
    public function handlePluginRoute(array $route)
    {
        $currentStep = $route[0];
        array_shift($route);
        switch ($currentStep) {
            case 'config':
                $this->configureAntCMS($route);
                break;

            case 'pages':
                $this->managePages($route);
                break;

            default:
                echo "Unrecognized route: " . $currentStep;
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

        switch ($route[0]) {
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
        $HTMLTemplate = $antCMS->getThemeTemplate('textarea_edit_layout');
        $pages = AntPages::getPages();
        $currentConfig = AntConfig::currentConfig();

        switch ($route[0]) {
            case 'regenerate':
                AntPages::generatePages();
                header('Location: //' . $currentConfig['baseURL'] . "plugin/admin/pages/");
                exit;

            case 'edit':
                array_shift($route);
                $pagePath = implode('/', $route);
                $page = file_get_contents(antContentPath . '/' . $pagePath);
                $HTMLTemplate = str_replace('<!--AntCMS-ActionURL-->', '//' . $currentConfig['baseURL'] . "plugin/admin/pages/save/$pagePath", $HTMLTemplate);
                $HTMLTemplate = str_replace('<!--AntCMS-TextAreaContent-->', htmlspecialchars($page), $HTMLTemplate);
                break;

            case 'save':
                array_shift($route);
                $pagePath = antContentPath . '/' . implode('/', $route);
                if (!$_POST['textarea']) {
                    header('Location: //' . $currentConfig['baseURL'] . "plugin/admin/pages/");
                }
                file_put_contents($pagePath, $_POST['textarea']);
                header('Location: //' . $currentConfig['baseURL'] . "plugin/admin/pages/");
                exit;

            default:
                $HTMLTemplate = "<h1>Page Management</h1>\n";
                $HTMLTemplate .= "<a href='//" . $currentConfig['baseURL'] . "plugin/admin/pages/regenerate'>Click here to regenerate the page list</a><br>\n";
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
