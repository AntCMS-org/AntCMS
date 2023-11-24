<?php

namespace Plugins\Admin;

use \Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Controller
{
    public function registerRoutes(App $app)
    {
        $adminPlugin = new Admin;

        $app->get('/admin', function (Request $request, Response $response) use ($adminPlugin) {
            $adminPlugin->checkAuth();
            $adminPlugin->setRequest($request);
            $adminPlugin->SetResponse($response);
            return $adminPlugin->renderIndex();
        });

        $app->get('/admin/config', function (Request $request, Response $response) use ($adminPlugin) {
            $adminPlugin->checkAuth();
            $adminPlugin->setRequest($request);
            $adminPlugin->SetResponse($response);
            return $adminPlugin->config();
        });

        $app->get('/admin/config/edit', function (Request $request, Response $response) use ($adminPlugin) {
            $adminPlugin->checkAuth();
            $adminPlugin->setRequest($request);
            $adminPlugin->SetResponse($response);
            return $adminPlugin->editConfig();
        });

        $app->post('/admin/config/save', function (Request $request, Response $response) use ($adminPlugin) {
            $adminPlugin->checkAuth();
            $adminPlugin->setRequest($request);
            $adminPlugin->SetResponse($response);
            return $adminPlugin->saveConfig();
        });

        $app->get('/admin/users', function (Request $request, Response $response) use ($adminPlugin) {
            $adminPlugin->checkAuth();
            $adminPlugin->setRequest($request);
            $adminPlugin->SetResponse($response);
            return $adminPlugin->users();
        });

        $app->get('/admin/users/add', function (Request $request, Response $response) use ($adminPlugin) {
            $adminPlugin->checkAuth();
            $adminPlugin->setRequest($request);
            $adminPlugin->SetResponse($response);
            return $adminPlugin->addUser();
        });

        $app->get('/admin/users/edit/{username}', function (Request $request, Response $response, array $args) use ($adminPlugin) {
            $adminPlugin->checkAuth();
            $adminPlugin->setRequest($request);
            $adminPlugin->SetResponse($response);
            return $adminPlugin->editUser($args);
        });

        $app->get('/admin/users/resetpassword/{username}', function (Request $request, Response $response, array $args) use ($adminPlugin) {
            $adminPlugin->checkAuth();
            $adminPlugin->setRequest($request);
            $adminPlugin->SetResponse($response);
            return $adminPlugin->resetpassword($args);
        });

        $app->post('/admin/user/save', function (Request $request, Response $response) use ($adminPlugin) {
            $adminPlugin->checkAuth();
            $adminPlugin->setRequest($request);
            $adminPlugin->SetResponse($response);
            return $adminPlugin->saveUser();
        });

        $app->post('/admin/user/savenew', function (Request $request, Response $response) use ($adminPlugin) {
            $adminPlugin->checkAuth();
            $adminPlugin->setRequest($request);
            $adminPlugin->SetResponse($response);
            return $adminPlugin->saveUser();
        });

        $app->get('/admin/pages/regenerate', function (Request $request, Response $response) use ($adminPlugin) {
            $adminPlugin->checkAuth();
            $adminPlugin->setRequest($request);
            $adminPlugin->SetResponse($response);
            return $adminPlugin->regeneratePages();
        });
    }
}
