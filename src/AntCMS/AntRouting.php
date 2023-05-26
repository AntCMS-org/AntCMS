<?php

namespace AntCMS;

class AntRouting
{
    private string $baseUrl;
    private string $requestUri;
    private array $uriExploded;

    private array $indexes = ['/', '/index.php', '/index.html', '', 'index.php', 'index.html'];

    public function __construct(string $baseUrl, string $requestUri)
    {
        $this->baseUrl = $baseUrl;
        $this->requestUri = $requestUri;
        $this->setExplodedUri($requestUri);
    }

    public function setRequestUri(string $requestUri): void
    {
        $this->$requestUri = $requestUri;
        $this->setExplodedUri($requestUri);
    }

    public function requestUriUnshift(string $append): void
    {
        array_unshift($this->uriExploded, $append);
        $this->requestUri = implode('/', $this->uriExploded);
    }

    public function redirectHttps(): void
    {
        $scheme = $_SERVER['HTTPS'] ?? $_SERVER['REQUEST_SCHEME'] ?? $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null;
        $isHttps = !empty($scheme) && (strcasecmp('on', $scheme) == 0 || strcasecmp('https', $scheme) == 0);

        if (!$isHttps) {
            $url = 'https://' . AntTools::repairURL($this->baseUrl . '/' . $this->requestUri);
            header('Location: ' . $url);
            exit;
        }
    }

    public function checkMatch(string $uri): bool
    {
        $matching =  explode('/', $uri);
        if (empty($matching[0])) {
            array_shift($matching);
        }

        if (count($matching) < count($this->uriExploded) && end($matching) !== '*') {
            return false;
        }

        foreach ($this->uriExploded as $index => $value) {
            if (isset($matching[$index]) && $matching[$index] !== '*' && $matching[$index] !== $value) {
                return false;
            }
        }

        return true;
    }

    public function routeToPlugin(): void
    {
        $pluginName = $this->uriExploded[1];
        $pluginLoader = new AntPluginLoader();
        $plugins = $pluginLoader->loadPlugins();

        //Drop the first two elements of the array so the remaining segments are specific to the plugin.
        array_splice($this->uriExploded, 0, 2);

        foreach ($plugins as $plugin) {
            if (strtolower($plugin->getName()) === strtolower($pluginName)) {
                $plugin->handlePluginRoute($this->uriExploded);
                exit;
            }
        }

        // plugin not found
        header("HTTP/1.0 404 Not Found");
        echo ("Error 404");
        exit;
    }

    public function isIndex(): bool
    {
        return (in_array($this->requestUri, $this->indexes));
    }

    private function setExplodedUri(string $uri): void
    {
        $exploaded = explode('/', $uri);

        if (empty($exploaded[0])) {
            array_shift($exploaded);
        }

        $this->uriExploded = $exploaded;
    }
}
