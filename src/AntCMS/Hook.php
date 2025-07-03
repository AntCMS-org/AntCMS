<?php

/**
 * Copyright 2025 AntCMS
 */

namespace AntCMS;

/**
 * This class isn't intended to be directly referenced.
 * Use the HookController class instead!
 */
class Hook
{
    public readonly string $name;
    public int $timesFired = 0;
    public int $registeredCallbacks = 0;

    /** @var callable[] */
    private array $callbacks = [];

    /**
     * Creates a new instance of a hook
     *
     * @param string $name The name of the hook
     * @param string $description A description of this hook
     * @param bool $isDefaultPreventable Marks if the default behavior for a hook can be prevented.
     */
    public function __construct(string $name, public string $description, public readonly bool $isDefaultPreventable = false)
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
    public function fire(array $params): Event
    {
        $this->timesFired++;

        // Create the new event object with the originally provided parameters
        $event = new Event($this->name, $params, $this->registeredCallbacks, $this->isDefaultPreventable);

        // Then fire each of the callbacks and update the event instance from each one.
        foreach ($this->callbacks as $callback) {
            $newEvent = call_user_func($callback, $event);
            if ($newEvent instanceof Event) {
                $event = $newEvent;
            }

            // Mark that callback as done
            $event->next();
        }

        return $event;
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
