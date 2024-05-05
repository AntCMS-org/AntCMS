<?php

namespace AntCMS;

class HookController
{
    /** @var Hook[] */
    private static array $hooks = [];

    public static function getHookList(): array
    {
        return self::$hooks;
    }

    public static function isRegistered(string $name): bool
    {
        return array_key_exists($name, self::$hooks);
    }

    public static function registerHook(string $name, string $description = ''): bool
    {
        if (self::isRegistered($name)) {
            if ($description !== '') {
                self::$hooks[$name]->$description = $description;
            }
            return true;
        }

        self::$hooks[$name] = new Hook($name, $description);
        return true;
    }

    public static function registerCallback(string $name, callable $callback): void
    {
        if (!self::isRegistered($name)) {
            self::registerHook($name, '');
        }
        self::$hooks[$name]->registerCallback($callback);
    }

    public static function fire(string $name, array $params): void
    {
        if (self::isRegistered($name)) {
            self::$hooks[$name]->fire($params);
        } else {
            error_log("Hook '$name' is not registed and cannot be fired");
        }
    }
}
