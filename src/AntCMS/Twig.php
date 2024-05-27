<?php

namespace AntCMS;

use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;

class Twig
{
    private static ?Environment $twigEnvironment = null;
    private static ?string $theme = null;

    public static function registerTwig(?Environment $twigEnvironment = null, ?string $theme = null): void
    {
        self::$theme = $theme ?? CURRENT_THEME;

        if (!is_null($twigEnvironment)) {
            self::$twigEnvironment = $twigEnvironment;
        } else {
            $twigEnvironment = new Environment(
                new ChainLoader([
                    new FilesystemLoader([
                        PATH_THEMES . DIRECTORY_SEPARATOR . self::$theme . DIRECTORY_SEPARATOR . 'Templates',
                        PATH_THEMES . DIRECTORY_SEPARATOR . 'Default' . DIRECTORY_SEPARATOR . 'Templates',
                    ]),
                    new ArrayLoader(),
                ]),
                [
                    'cache' => (Config::get('performance.cacheMode') !== 'none') ? PATH_CACHE : false,
                    'debug' => DEBUG_LEVEL >= 2,
                    'use_yield' => false,
                ]
            );
            $twigEnvironment->addExtension(new TwigFilters());
            $twigEnvironment->addGlobal('AntCMSSiteTitle', Config::get('siteInfo')['title']);
            self::$twigEnvironment = $twigEnvironment;
        }
    }

    public static function setArrayLoaderTemplate(string $name, string $template): void
    {
        $loaders = self::$twigEnvironment->getLoader()->getLoaders(); /** @phpstan-ignore-line */
        foreach ($loaders as $loader) {
            if (method_exists($loader, 'setTemplate')) {
                $loader->setTemplate($name, $template);
            }
        }
    }

    public static function addLoaderPath(string $path, string $namespace = FilesystemLoader::MAIN_NAMESPACE): void
    {
        $loaders = self::$twigEnvironment->getLoader()->getLoaders(); /** @phpstan-ignore-line */
        foreach ($loaders as $loader) {
            if (method_exists($loader, 'addPath')) {
                $loader->addPath($path, $namespace);
            }
        }
    }

    public static function templateExists(string $name): bool
    {
        return self::$twigEnvironment->getLoader()->exists($name);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function render(string $template, array $data = []): string
    {
        return self::$twigEnvironment->render($template, $data);
    }
}
