<?php

namespace AntCMS;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use AntCMS\Tools;
use AntCMS\Config;
use AntCMS\Markdown;

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
        return '//' . Tools::repairURL(Config::currentConfig('baseURL') . '/' . trim($relative));
    }

    public function markdown(string $content): string
    {
        return Markdown::renderMarkdown($content);
    }
}
