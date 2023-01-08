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
        switch ($route[0]) {
            case 'config':
                $this->configureAntCMS($route);
                break;

            case 'pages':
                $this->mangePages($route);
                break;

            default:
                echo "Unrecognized route: " . $route[0];
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
        $currentConfig = AntConfig::currentConfig();

        $markdown = "# AntCMS Configuration \r\n";

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


        $pageTemplate = str_replace('<!--AntCMS-Title-->', 'AntCMS Configuration', $pageTemplate);
        $pageTemplate = str_replace('<!--AntCMS-Body-->', AntMarkdown::renderMarkdown($markdown), $pageTemplate);

        echo $pageTemplate;
        exit;
    }

    private function mangePages(array $route)
    {
        $antCMS = new AntCMS;
        $pageTemplate = $antCMS->getPageLayout();
        $pages = AntPages::getPages();

        $markdown = "# Page Management \r\n";

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
