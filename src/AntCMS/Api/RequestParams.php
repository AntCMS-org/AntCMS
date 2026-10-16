<?php

/**
 * Copyright 2026 AntCMS
 */

namespace AntCMS\Api;

use AntCMS\Tools;
use Flight;
use flight\util\Collection;

class RequestParams
{
    /**
     * @var string[]
     */
    public array $urlParams;
    public Collection $post;
    public Collection $get;
    public string $rawBody;

    /**
     * If the request of contained valid JSON, it will be decoded here.
     * @var mixed[]|null
     */
    public ?array $bodyJson = null;

    public function __construct(string $plugin, string $method)
    {
        // Some needed variable setup
        $url = rtrim(Tools::getUri(), '/');
        $startingString = "/{$plugin}/{$method}/";

        // Split the request URL, find the parameters for the current API call, and then parse them
        $pos = strpos($url, $startingString);
        if ($pos === false) {
            $urlParams = [];
        } else {
            $length = strlen($startingString);
            $urlParams = explode('/', substr($url, $pos + $length));
        }

        $this->urlParams = $urlParams;
        $this->post = Flight::request()->data;
        $this->get = Flight::request()->query;
        $this->rawBody = Flight::request()->body;

        if (json_validate(Flight::request()->body)) {
            $this->bodyJson = json_decode(Flight::request()->body, true);
        }
    }
}
