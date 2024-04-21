<?php

namespace AntCMS;

use AntCMS\Config;
use AntCMS\TwigFilters;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;

class Twig
{
    private static ?Environment $twigEnvironment = null;
    private static ?string $theme = null;

    public static function registerTwig(?Environment $environment = null): void
    {
        self::$theme = Config::currentConfig('activeTheme');

        if (!is_null($environment)) {
            self::$twigEnvironment = $environment;
        } else {
            $environment = new Environment(
                new ChainLoader([
                    new FilesystemLoader([
                        antThemePath . DIRECTORY_SEPARATOR . self::$theme . DIRECTORY_SEPARATOR . 'Templates',
                        antThemePath . DIRECTORY_SEPARATOR . 'Default' . DIRECTORY_SEPARATOR . 'Templates',
                    ]),
                    new ArrayLoader(),
                ]),
                [
                    'cache' => (Config::currentConfig('enableCache') !== 'none') ? AntCachePath : false,
                    'debug' => Config::currentConfig('debug'),
                    'use_yield' => false,
                ]
            );
            $environment->addExtension(new TwigFilters());
            $environment->addGlobal('AntCMSSiteTitle', AntCMS::getSiteInfo()['siteTitle']);
            self::$twigEnvironment = $environment;
        }
    }

    private static function doSelfCheck(): void
    {
        if (is_null(self::$twigEnvironment)) {
            self::registerTwig();
        }
    }

    public static function setArrayLoaderTemplate(string $name, string $template): void
    {
        self::doSelfCheck();
        $loaders = self::$twigEnvironment->getLoader()->getLoaders(); /** @phpstan-ignore-line */
        foreach ($loaders as $loader) {
            if (method_exists($loader, 'setTemplate')) {
                $loader->setTemplate($name, $template);
            }
        }
    }

    public static function addLoaderPath(string $path, string $namespace = FilesystemLoader::MAIN_NAMESPACE): void
    {
        self::doSelfCheck();
        $loaders = self::$twigEnvironment->getLoader()->getLoaders(); /** @phpstan-ignore-line */
        foreach ($loaders as $loader) {
            if (method_exists($loader, 'addPath')) {
                $loader->addPath($path, $namespace);
            }
        }
    }

    public static function templateExists(string $name): bool
    {
        self::doSelfCheck();
        return self::$twigEnvironment->getLoader()->exists($name);
    }

    public static function render(string $template, array $data = []): string
    {
        self::doSelfCheck();
        return self::$twigEnvironment->render($template, $data);
    }
}
