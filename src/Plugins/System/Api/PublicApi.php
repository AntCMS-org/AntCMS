<?php

declare(strict_types=1);

/**
 * Copyright 2026 AntCMS
 */

namespace AntCMS\Plugins\System\Api;

use AntCMS\Api\RequestParams;
use AntCMS\Api\Response;

class PublicApi
{
    public function status(RequestParams $requestParams): Response
    {
        return new Response('okay');
    }
}
