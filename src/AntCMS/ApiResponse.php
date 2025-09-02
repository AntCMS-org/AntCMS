<?php

/**
 * Copyright 2025 AntCMS
 */

namespace AntCMS;

use JsonSerializable;

class ApiResponse implements JsonSerializable
{
    public function __construct(private readonly mixed $result, private readonly bool $error = false, private readonly int $code = 200, private readonly string $message = '')
    {
    }

    public function jsonSerialize(): mixed
    {
        return $this->result;
    }

    /**
     * @return array<string, string>
     */
    public function getBody(): array
    {
        $result = [
            'status' => $this->error ? 'error' : 'success',
        ];

        if ($this->message !== '') {
            $result['message'] = $this->message;
        }

        if ($this->result) {
            $result['result'] = $this->result;
        }

        return $result;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function isError(): bool
    {
        return $this->error;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getResult(): mixed
    {
        return $this->result;
    }
}
