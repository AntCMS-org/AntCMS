<?php

declare(strict_types=1);

/**
 * Copyright 2026 AntCMS
 */

namespace AntCMS;

class Environment
{
    public static function isCli(): bool
    {
        return (php_sapi_name() === 'cli' || (http_response_code() === 0 || http_response_code() === false));
    }

    public static function isPHPDevServer(): bool
    {
        return PHP_SAPI === 'cli-server';
    }
}
