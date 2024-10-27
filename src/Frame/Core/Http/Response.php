<?php

namespace Frame\Core\Http;

class Response extends Message implements HttpConstants
{
    protected int $statusCode = self::HTTP_OK;
    protected string $reasonPhrase = '';
    protected array $cookies = [];

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function withStatus(int $code, string $reasonPhrase = ''): static
    {
        $new = clone $this;
        $new->statusCode = $code;
        $new->reasonPhrase = $reasonPhrase;
        return $new;
    }

    public function json(mixed $data): static
    {
        $new = clone $this;
        $new->headers[HttpConstants::HEADER_CONTENT_TYPE] = [HttpConstants::CONTENT_TYPE_JSON];
        $new->body = json_encode($data, JSON_THROW_ON_ERROR);
        return $new;
    }

    public function html(string $content): static
    {
        $new = clone $this;
        $new->headers[HttpConstants::HEADER_CONTENT_TYPE] = [HttpConstants::CONTENT_TYPE_HTML];
        $new->body = $content;
        return $new;
    }

    public function withCookie(
        string $name,
        string $value,
        int    $expires = 0,
        string $path = '/',
        string $domain = '',
        bool   $secure = false,
        bool   $httpOnly = true,
        string $sameSite = 'Lax'
    ): static
    {
        $new = clone $this;
        $new->cookies[$name] = compact('value', 'expires', 'path', 'domain', 'secure', 'httpOnly', 'sameSite');
        return $new;
    }

    public function send(): void
    {
        // Send status code
        http_response_code($this->statusCode);

        // Send headers
        foreach ($this->headers as $name => $values) {
            foreach ($values as $value) {
                header("$name: $value", false);
            }
        }

        // Send cookies
        foreach ($this->cookies as $name => $params) {
            setcookie(
                $name,
                $params['value'],
                array_filter([
                    'expires' => $params['expires'],
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httpOnly'],
                    'samesite' => $params['sameSite']
                ])
            );
        }

        // Send body
        echo $this->body;
    }
}