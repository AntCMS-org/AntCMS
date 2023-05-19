<?php

namespace AntCMS;

class AntEnviroment
{
    public static function isCli(): bool
    {
        return (php_sapi_name() === 'cli' || !http_response_code());
    }
}
