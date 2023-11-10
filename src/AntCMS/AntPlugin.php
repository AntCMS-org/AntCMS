<?php

namespace AntCMS;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\App;

abstract class AntPlugin
{
    protected ?Request $request;
    protected ?Response $response;

    public function setRequest(?Request $request)
    {
        $this->request = $request;
    }

    public function SetResponse(?Response $response)
    {
        $this->response = $response;
    }

    /** @return string  */
    abstract function getName();
}
