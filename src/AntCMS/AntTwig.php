<?php

namespace AntCMS;

use AntCMS\AntConfig;
use AntCMS\AntTwigFilters;

class AntTwig
{
    /** 
     * @param array<mixed> $params 
     * @param string|null $theme 
     * @return string 
     */
    public static function renderWithTiwg(string $content = '', array $params = array(), string $theme = null)
    {
        $twigCache = AntConfig::currentConfig('enableCache') ? AntCachePath : false;
        $theme = $theme ?? AntConfig::currentConfig('activeTheme');

        if (!is_dir(antThemePath . '/' . $theme)) {
            $theme = 'Default';
        }

        $templatePath = AntTools::repairFilePath(antThemePath . '/' . $theme . '/' . 'Templates');

        $filesystemLoader = new \Twig\Loader\FilesystemLoader($templatePath);
        $stringLoader = new \Shapecode\Twig\Loader\StringLoader();
        $chainLoader = new \Twig\Loader\ChainLoader([$stringLoader, $filesystemLoader]);
        $twigEnvironment = new \Twig\Environment($chainLoader, [
            'cache' => $twigCache,
            'debug' => AntConfig::currentConfig('debug'),
        ]);

        $twigEnvironment->addExtension(new \AntCMS\AntTwigFilters);

        return $twigEnvironment->render($content, $params);
    }
}
