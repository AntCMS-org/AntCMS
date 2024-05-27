<?php

namespace AntCMS;

/**
 * This class isn't intended to be directly referenced.
 * Use the HookController class instead!
 */
class Hook
{
    public string $name;
    public int $timesFired = 0;
    public int $registeredCallbacks = 0;

    /** @var callable[] */
    private array $callbacks = [];

    /**
     * Creates a new instance of a hook
     *
     * @param string $name The name of the hook
     * @param string $description A description of this hook
     */
    public function __construct(string $name, public string $description)
    {
        if (preg_match('/^\w+$/', $name) === 0 || preg_match('/^\w+$/', $name) === false) {
            throw new \Exception("The hook name '$name' is invalid. Only a-z A-Z, 0-9, and _ are allowed to be in the hook name.");
        }

        $this->name = $name;
    }

    /**
     * Fires the hook
     *
     * @param mixed[] $params An array of values to pass to the callbacks registered for this hook
     */
    public function fire(array $params): void
    {
        $this->timesFired++;
        foreach ($this->callbacks as $callback) {
            call_user_func($callback, $params);
        }
    }

    /**
     * Registers a callback
     */
    public function registerCallback(callable $callback): void
    {
        $this->registeredCallbacks++;
        $this->callbacks[] = $callback;
    }
}
