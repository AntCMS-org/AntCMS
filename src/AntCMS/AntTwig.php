<?php

namespace AntCMS;

use AntCMS\AntConfig;

class AntTwig
{
    public function renderWithTiwg(string $content = '', array $params = array(), string $theme = null)
    {
        $twigCache = AntConfig::currentConfig('enableCache') ? AntCachePath : false;
        $theme = $theme ?? AntConfig::currentConfig('activeTheme');

        if (!is_dir(antThemePath . '/' . $theme)) {
            $theme = 'Default';
        }

        $templatePath = AntTools::repairFilePath(antThemePath . '/' . $theme . '/' . 'Templates');

        $loaderFilesystem = new \Twig\Loader\FilesystemLoader($templatePath);
        $loaderString = new \Shapecode\Twig\Loader\StringLoader();
        $loader = new \Twig\Loader\ChainLoader([$loaderString, $loaderFilesystem]);
        $twig = new \Twig\Environment($loader, [
            'cache' => $twigCache,
            'debug' => AntConfig::currentConfig('debug'),
        ]);

        return $twig->render($content, $params);
    }
}
