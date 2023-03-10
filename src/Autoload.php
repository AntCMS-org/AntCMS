<?php

spl_autoload_register(static function ($class) {
    $class = str_replace('\\', '/', $class);
    $path = __DIR__ . '/' . $class . '.php';
    if (is_readable($path)) {
        require_once $path;
    }
});
