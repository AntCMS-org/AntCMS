<?php

use AntCMS\AntCMS;
use AntCMS\AntPlugin;
use AntCMS\AntConfig;
use AntCMS\AntPages;
use AntCMS\AntMarkdown;

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
                $this->mangePages($route);
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
        $currentConfig = file_get_contents(antConfigFile);

        //$markdown = "# AntCMS Configuration \r\n";
        /*
        foreach ($currentConfig as $key => $value) {
            if (is_array($value)) {
                $markdown .= " - $key: \r\n";
                foreach ($value as $key => $value) {
                    $value = is_bool($value) ? $this->boolToWord($value) : $value;
                    $markdown .= "   - $key: $value \r\n";
                }
            } else {
                $value = is_bool($value) ? $this->boolToWord($value) : $value;
                $markdown .= " - $key: $value \r\n";
            }
        }
        */

        $content = '<form>';
        $content .= '<textarea cols="100" type="text" class="form-textarea">'. htmlspecialchars($currentConfig) . '</textarea>';
        $content .= '</form>';              

        $pageTemplate = str_replace('<!--AntCMS-Title-->', 'AntCMS Configuration', $pageTemplate);
        $pageTemplate = str_replace('<!--AntCMS-Body-->', $content, $pageTemplate);

        echo $pageTemplate;
        exit;
    }

    private function mangePages(array $route)
    {
        $antCMS = new AntCMS;
        $pageTemplate = $antCMS->getPageLayout();
        $pages = AntPages::getPages();
        $currentConfig = AntConfig::currentConfig();

        if ($route[0] == 'regenerate') {
            AntPages::generatePages();
            header('Location: //' . $currentConfig['baseURL'] . "plugin/admin/pages/");
            exit;
        }

        $markdown = "# Page Management \r\n";
        $markdown .= "[Click here to regenerate the page list](" . '//' . $currentConfig['baseURL'] . "plugin/admin/pages/regenerate) \r\n";

        foreach ($pages as $page) {
            $markdown .= '## ' . $page['pageTitle'] . "\r\n";
            $markdown .= '- Full page path: ' . $page['fullPagePath'] . "\r\n";
            $markdown .= '- Functional page path: ' . $page['functionalPagePath'] . "\r\n";
            $markdown .= '- Show in navbar: ' . $this->boolToWord($page['showInNav']) . "\r\n";
        }

        $pageTemplate = str_replace('<!--AntCMS-Title-->', 'AntCMS Page Management', $pageTemplate);
        $pageTemplate = str_replace('<!--AntCMS-Body-->', AntMarkdown::renderMarkdown($markdown), $pageTemplate);

        echo $pageTemplate;
        exit;
    }

    private function boolToWord($value)
    {
        return boolval($value) ? 'true' : 'false';
    }
}
