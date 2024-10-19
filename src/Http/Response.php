<?php

namespace Http;

class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private string $body = '';
    private array $cookies = [];

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

    public function setCookie(string $name, string $value, int $expires = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = false): void
    {
        $this->cookies[] = [
            'name' => $name,
            'value' => $value,
            'expires' => $expires,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httpOnly' => $httpOnly,
        ];
    }

    public function getCookie(string $name): string
    {
        foreach ($this->cookies as $cookie) {
            if ($cookie['name'] === $name) {
                return $cookie['value'];
            }
        }

        return '';
    }

    public function clearCookie(string $name): void
    {
        foreach ($this->cookies as $key => $cookie) {
            if ($cookie['name'] === $name) {
                unset($this->cookies[$key]);
            }
        }
        setcookie($name, '', time() - 3600);
    }

    public function deleteCookie(string $name): void
    {
        foreach ($this->cookies as $key => $cookie) {
            if ($cookie['name'] === $name) {
                unset($this->cookies[$key]);
            }
        }
        setcookie($name, '', time() - 3600, '/');
    }

    public
    function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        echo $this->body;
    }
}
