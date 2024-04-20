<?php

namespace AntCMS;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use AntCMS\Tools;
use AntCMS\Config;

class TwigFilters extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('absUrl', [$this, 'absUrl']),
        ];
    }

    public function absUrl(string $relative): string
    {
        return '//' . Tools::repairURL(Config::currentConfig('baseURL') . '/' . trim($relative));
    }
}
