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
    private bool $isDone = false;

    /**
     * @param string $associatedHook The hook that this event is associated with. Hook must exist.
     *
     * @param mixed[] $parameters
     */
    public function __construct(string $associatedHook, private array $parameters)
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
        return $this->isDone;
    }

    /**
     * Marks the hook as done and fires a `onHookFireComplete` event.
     */
    public function markDone(): static
    {
        if (!$this->isDone()) {
            // Mark the event done and calculate it's timing
            $this->isDone = true;
            $this->timeElapsed = hrtime(true) - $this->hrStart;

            // Ensure the `onHookFireComplete` when this event is completed
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
     */
    public function setParameters(array $parameters): static
    {
        $this->parameters = $parameters;
        $this->paramUpdateCount++;
        return $this;
    }

    /**
     * Returns the number of times the event parameters were read from
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
}
