<?php

/**
 * Copyright 2025 AntCMS
 */

namespace AntCMS;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigFilters extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('absUrl', $this->absUrl(...)),
            new TwigFilter('markdown', $this->markdown(...), ['is_safe' => ['html']]),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('publicApi', $this->publicApi(...)),
        ];
    }

    public function publicApi(string $plugin, string $method, array $data = []): mixed
    {
        $apiController = new ApiController();
        return $apiController->scriptablePublicController($plugin, $method, $data);
    }

    public function absUrl(string $relative): string
    {
        return '//' . Tools::repairURL(BASE_URL . '/' . trim($relative));
    }

    public function markdown(string $content): string
    {
        return Markdown::parse($content);
    }
}
