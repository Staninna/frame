<?php declare(strict_types=1);

namespace Frame\Core\Http;

/**
 * Base implementation of HTTP message functionality
 */
abstract class Message implements MessageInterface
{
    protected string $protocolVersion = '1.1';
    protected array $headers = [];
    protected string $body = '';

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion(string $version): static
    {
        $new = clone $this;
        $new->protocolVersion = $version;
        return $new;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader(string $name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }

    public function getHeader(string $name): array
    {
        $name = strtolower($name);
        return $this->headers[$name] ?? [];
    }

    public function getHeaderLine(string $name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    public function withHeader(string $name, string|array $value): static
    {
        $new = clone $this;
        $new->headers[strtolower($name)] = is_array($value) ? $value : [$value];
        return $new;
    }

    public function withAddedHeader(string $name, string|array $value): static
    {
        $new = clone $this;
        $name = strtolower($name);
        if (!isset($new->headers[$name])) {
            $new->headers[$name] = [];
        }
        $new->headers[$name] = array_merge(
            $new->headers[$name],
            is_array($value) ? $value : [$value]
        );
        return $new;
    }

    public function withoutHeader(string $name): static
    {
        $new = clone $this;
        unset($new->headers[strtolower($name)]);
        return $new;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function withBody(string $body): static
    {
        $new = clone $this;
        $new->body = $body;
        return $new;
    }
}
