<?php

namespace AntCMS;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use AntCMS\AntTools;
use AntCMS\AntConfig;

class AntTwigFilters extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('absUrl', [$this, 'absUrl']),
        ];
    }

    public function absUrl(string $relative): string
    {
        return '//' . AntTools::repairURL(AntConfig::currentConfig('baseURL') . '/' . $relative);
    }
}
