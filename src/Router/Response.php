<?php

namespace Router;

class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private string $body = '';

    public function setStatusCode(int $code): void
    {
        $this->statusCode = $code;
    }

    public function setHeader(string $name, string $value): void
    {
        $this->headers[$name] = $value;
    }

    public function write(string $content): void
    {
        $this->body .= $content;
    }

    public function json($data): void
    {
        $this->setHeader('Content-Type', 'application/json');
        $this->write(json_encode($data));
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        echo $this->body;
    }
}
