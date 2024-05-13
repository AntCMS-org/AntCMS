<?php

namespace AntCMS;

class ApiResponse
{
    public function __construct(private mixed $result, private bool $error = false, private int $code = 200, private string $message = '')
    {
    }

    public function getBody(): array
    {
        return [
            'status' => $this->error ? 'error' : 'success',
            'message' => $this->message,
            'result' => $this->result,
        ];
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
}
