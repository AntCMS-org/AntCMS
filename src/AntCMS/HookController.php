<?php

/**
 * Copyright 2025 AntCMS
 */

namespace AntCMS;

class HookController
{
    /** @var Hook[] */
    private static array $hooks = [];

    /**
     * @return Hook[]
     */
    public static function getHookList(): array
    {
        return self::$hooks;
    }

    public static function isRegistered(string $name): bool
    {
        return array_key_exists($name, self::$hooks);
    }

    public static function registerHook(string $name, string $description = '', bool $isDefaultPreventable = false): bool
    {
        if (self::isRegistered($name)) {
            if ($description !== '') {
                self::$hooks[$name]->description = $description;
            }
            return true;
        }

        self::$hooks[$name] = new Hook($name, $description, $isDefaultPreventable);
        return true;
    }

    public static function registerCallback(string $name, callable $callback): void
    {
        if (!self::isRegistered($name)) {
            self::registerHook($name, '');
        }
        self::$hooks[$name]->registerCallback($callback);
    }

    /**
     * @param mixed[] $params (Optional)
     */
    public static function fire(string $name, array $params = []): Event
    {
        if (self::isRegistered($name)) {
            return self::$hooks[$name]->fire($params);
        }
        throw new \Exception("Hook '{$name}' is not registered and cannot be fired");
    }
}
