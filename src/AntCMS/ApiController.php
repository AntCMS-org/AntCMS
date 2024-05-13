<?php

namespace AntCMS;

use Flight;

class ApiController
{
    private function sendResponse(ApiResponse $apiResponse): void
    {
        $code = $apiResponse->getCode();
        if ($apiResponse->isError()) {
            $message = $apiResponse->getMessage();
            error_log("($code) $message");
        }

        Flight::json($apiResponse->getBody(), $code);
    }

    private function call(string $type, string $plugin, string $method): void
    {
        $apiFqcn = "\AntCMS\\Plugins\\" . ucfirst($plugin) . "\\Api\\" . ucfirst($type) . 'Api';

        // Send an error if the entrypoint doesn't exist
        if (!class_exists($apiFqcn)) {
            $response = new ApiResponse('', true, 404, "API entrypoint '$type/$plugin' does not exist");
            $this->sendResponse($response);
            return;
        }

        // Now instance the API for that plugin
        $api = new $apiFqcn();

        // Send an error if the endpoint doesn't exist
        if (!method_exists($api, $method)) {
            $response = new ApiResponse('', true, 404, "API endpoint '$type/$plugin/$method' does not exist");
            $this->sendResponse($response);
            return;
        }

        $hookData = ['plugin' => ucfirst($plugin), 'method' => $method];
        HookController::fire('beforeApiCalled', $hookData);

        // Sanity checks passed, now actually process the request
        try {
            $response = $api->$method();
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $location = str_replace(PATH_ROOT . DIRECTORY_SEPARATOR, '', $e->getFile()) . ':' . $e->getLine();

            // Create a generic HTTP 500 response
            $response = new ApiResponse('', true, 500, "An internal error occured");

            // Log the actual message for aid in debugging
            error_log("Fatal error: $message ($location)");
        }

        $hookData['response'] = $response;
        HookController::fire('afterApiCalled', $hookData);

        $this->sendResponse($response);
    }

    public function publicController(string $plugin, string $method): void
    {
        $this->call('public', $plugin, $method);
    }

    public function privateController(string $plugin, string $method): void
    {
        // TODO
        $apiResponse = new ApiResponse('', true, 501, "Protected API endpoints are not yet implemented in AntCMS");
        $this->sendResponse($apiResponse);
    }
}
