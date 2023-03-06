<?php

namespace AntCMS;

use AntCMS\AntConfig;

class AntTwig
{
    protected $twigEnvironment;
    protected $theme;

    public function __construct(string $theme = null)
    {
        $twigCache = AntConfig::currentConfig('enableCache') ? AntCachePath : false;
        $this->theme = $theme ?? AntConfig::currentConfig('activeTheme');

        if (!is_dir(antThemePath . '/' . $this->theme)) {
            $this->theme = 'Default';
        }

        $this->twigEnvironment = new \Twig\Environment(new \Shapecode\Twig\Loader\StringLoader(), [
            'cache' => $twigCache,
            'debug' => AntConfig::currentConfig('debug'),
        ]);

        $this->twigEnvironment->addExtension(new \AntCMS\AntTwigFilters);
    }

    public function renderWithSubLayout(string $layout, array $params = array())
    {
        $subLayout = AntCMS::getThemeTemplate($layout, $this->theme);
        $mainLayout = AntCMS::getPageLayout($this->theme);
        $params['AntCMSBody'] = $this->twigEnvironment->render($subLayout, $params);

        return $this->twigEnvironment->render($mainLayout, $params);
    }

    public function renderWithTiwg(string $content = '', array $params = array())
    {
        return $this->twigEnvironment->render($content, $params);
    }
}
