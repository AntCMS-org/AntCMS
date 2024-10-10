<?php

namespace AntCMS;

use DateTime;

class Event
{
    private readonly string $associatedHook;
    private readonly DateTime $firedAt;
    private readonly float|int $hrStart;
    private int|float $timeElapsed;

    private int $paramUpdateCount = 0;
    private int $paramReadCount = 0;
    private int $lastCallback = 0;
    private bool $defaultPrevented = false;

    /**
     * @param string $associatedHook The hook that this event is associated with. Hook must exist.
     *
     * @param mixed[] $parameters
     */
    public function __construct(string $associatedHook, private array $parameters, private readonly int $totalCallbacks, private readonly bool $preventable)
    {
        if (!HookController::isRegistered($associatedHook)) {
            throw new \Exception("Hook $associatedHook is not registered!");
        }

        $this->associatedHook = $associatedHook;
        $this->firedAt = new DateTime();
        $this->hrStart = hrtime(true);
    }

    /**
     * Indicates if the hook has completed
     */
    public function isDone(): bool
    {
        return $this->lastCallback === $this->totalCallbacks;
    }

    /**
     * Called by the hook after each callback is complete.
     * Tracks if the event is completed & fires 'onHookFireComplete' when needed.
     *
     * Callbacks should not call this function.
     *
     * @return Event
     */
    public function next(): Event
    {
        if (!$this->isDone()) {
            // We just completed a callback, increment the last callback number.
            ++$this->lastCallback;

            // Check if that was the last callback & we are done
            if ($this->isDone()) {
                // Set the timing
                $this->timeElapsed = hrtime(true) - $this->hrStart;

                // Fire `onHookFireComplete`
                if ($this->associatedHook !== 'onHookFireComplete') {
                    HookController::fire('onHookFireComplete', [
                        'name' => $this->associatedHook,
                        'firedAt' => $this->firedAt,
                        'timeElapsed' => $this->timeElapsed,
                        'parameterReadCount' => $this->paramReadCount,
                        'parameterUpdateCount' => $this->paramUpdateCount,
                    ]);
                }
            }
        } else {
            // We shouldn't run into this situation, but it's non-fatal so only trigger a warning.
            trigger_error("The 'next' event was called too many times for the '$this->associatedHook' event. Event timing may be inaccurate and 'onHookFireComplete' would have been fired too soon.", E_USER_WARNING);
        }

        return $this;
    }

    /**
     * Indicates when the event was originally fired at
     */
    public function firedAt(): DateTime
    {
        return $this->firedAt;
    }

    /**
     * Returns the total time spent for this event in nanoseconds
     */
    public function timeElapsed(): int|float
    {
        return $this->timeElapsed;
    }

    /**
     * Gets the associated hook name
     */
    public function getHookName(): string
    {
        return $this->associatedHook;
    }

    /**
     * Gets the event parameters
     *
     * @return mixed[]
     */
    public function getParameters(): array
    {
        $this->paramReadCount++;
        return $this->parameters;
    }

    /**
     * Updates the parameters
     *
     * @param mixed[] $parameters
     *
     * @return Event
     */
    public function setParameters(array $parameters): Event
    {
        $this->parameters = $parameters;
        $this->paramUpdateCount++;
        return $this;
    }

    /**
     * Returns the number of times the event parameters were read from.
     */
    public function getReadCount(): int
    {
        return $this->paramReadCount;
    }

    /**
     * Returns the number of times the event parameters were updated.
     */
    public function getUpdateCount(): int
    {
        return $this->paramUpdateCount;
    }

    /**
     * Indicates if the default behavior for an event is preventable.
     */
    public function isDefaultPreventable(): bool
    {
        return $this->preventable;
    }

    /**
     * Indicates if the default behavior for an event is prevented.
     */
    public function isDefaultPrevented(): bool
    {
        return $this->defaultPrevented;
    }

    /**
     * Sets a flag for the default behavior of this event to be prevented.
     * Not all events can be prevented. Triggers a non-fatal error if the event's default behavior is not preventable.
     *
     * @return Event
     */
    public function preventDefault(): Event
    {
        if (!$this->isDefaultPreventable()) {
            trigger_error("The default behavior for the `$this->associatedHook` hook cannot be prevented.");
        } else {
            $this->defaultPrevented = true;
        }

        return $this;
    }
}
