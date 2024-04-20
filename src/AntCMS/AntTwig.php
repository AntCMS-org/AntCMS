<?php

namespace AntCMS;

use AntCMS\AntConfig;

class AntTwig
{
    protected \Twig\Environment $twigEnvironment;
    protected $theme;

    public function __construct(string $theme = null)
    {
        $twigCache = (AntConfig::currentConfig('enableCache') !== 'none') ? AntCachePath : false;
        $this->theme = $theme ?? AntConfig::currentConfig('activeTheme');

        if (!is_dir(antThemePath . DIRECTORY_SEPARATOR . $this->theme)) {
            $this->theme = 'Default';
        }

        $this->twigEnvironment = new \Twig\Environment(new \Shapecode\Twig\Loader\StringLoader(), [
            'cache' => $twigCache,
            'debug' => AntConfig::currentConfig('debug'),
        ]);

        $this->twigEnvironment->addExtension(new \AntCMS\AntTwigFilters());
    }

    public function renderWithSubLayout(string $layout, array $params = []): string
    {
        $subLayout = AntCMS::getThemeTemplate($layout, $this->theme);
        $mainLayout = AntCMS::getPageLayout($this->theme);
        $siteInfo = AntCMS::getSiteInfo();

        $params['AntCMSSiteTitle'] = $siteInfo['siteTitle'];
        $params['AntCMSBody'] = $this->twigEnvironment->render($subLayout, $params);

        return $this->twigEnvironment->render($mainLayout, $params);
    }

    public function renderWithTiwg(string $content = '', array $params = []): string
    {
        $siteInfo = AntCMS::getSiteInfo();
        $params['AntCMSSiteTitle'] = $siteInfo['siteTitle'];

        return $this->twigEnvironment->render($content, $params);
    }
}
