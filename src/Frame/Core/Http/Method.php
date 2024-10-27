<?php declare(strict_types=1);

namespace Frame\Core\Http;

use InvalidArgumentException;

enum Method: string
{
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
    case PATCH = 'PATCH';
    case HEAD = 'HEAD';
    case OPTIONS = 'OPTIONS';
    case TRACE = 'TRACE';
    case CONNECT = 'CONNECT';

    public static function fromString(string $method): self
    {
        return match (strtoupper($method)) {
            'GET' => self::GET,
            'POST' => self::POST,
            'PUT' => self::PUT,
            'DELETE' => self::DELETE,
            'PATCH' => self::PATCH,
            'HEAD' => self::HEAD,
            'OPTIONS' => self::OPTIONS,
            'TRACE' => self::TRACE,
            'CONNECT' => self::CONNECT,
            default => throw new InvalidArgumentException("Invalid HTTP method: $method")
        };
    }

    public function equals(Method $other): bool
    {
        return $this === $other;
    }

    /**
     * Returns whether the method is idempotent.
     *
     * Idempotent methods are safe to retry multiple times without side effects.
     *
     * @return bool
     */
    public function isIdempotent(): bool
    {
        return match ($this) {
            self::GET, self::HEAD, self::PUT, self::DELETE, self::OPTIONS, self::TRACE => true,
            default => false
        };
    }

    /**
     * Returns whether the method is safe.
     *
     * Safe methods are idempotent and do not have side effects.
     *
     * @return bool
     */
    public function isSafe(): bool
    {
        return match ($this) {
            self::GET, self::HEAD, self::OPTIONS, self::TRACE => true,
            default => false
        };
    }
}