<?php declare(strict_types=1);

namespace Frame\Core\Http;

/**
 * Interface for HTTP messages (both requests and responses)
 */
interface MessageInterface
{
    public function getProtocolVersion(): string;

    public function withProtocolVersion(string $version): static;

    public function getHeaders(): array;

    public function hasHeader(string $name): bool;

    public function getHeader(string $name): array;

    public function getHeaderLine(string $name): string;

    public function withHeader(string $name, string|array $value): static;

    public function withAddedHeader(string $name, string|array $value): static;

    public function withoutHeader(string $name): static;

    public function getBody(): string;

    public function withBody(string $body): static;
}