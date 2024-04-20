<?php

namespace AntCMS;

class Enviroment
{
    public static function isCli(): bool
    {
        return (php_sapi_name() === 'cli' || !http_response_code());
    }
}
