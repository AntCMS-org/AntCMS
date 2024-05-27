<?php

namespace AntCMS;

class ApiResponse
{
    public function __construct(private mixed $result, private readonly bool $error = false, private readonly int $code = 200, private string $message = '')
    {
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
}
