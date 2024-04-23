<?php

namespace AntCMS;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TwigFilters extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('absUrl', [$this, 'absUrl']),
            new TwigFilter('markdown', [$this, 'markdown']),
        ];
    }

    public function absUrl(string $relative): string
    {
        return '//' . Tools::repairURL(baseUrl . '/' . trim($relative));
    }

    public function markdown(string $content): string
    {
        return Markdown::parse($content);
    }
}
