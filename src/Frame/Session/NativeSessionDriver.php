<?php

namespace Frame\Session;


class NativeSessionDriver implements SessionDriverInterface
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function start(): bool
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return true;
        }

        // Configure session settings
        session_set_cookie_params([
            'lifetime' => time() + ($this->config['lifetime'] * 60),
            'path' => $this->config['path'],
            'domain' => $this->config['domain'],
            'secure' => $this->config['secure'],
            'httponly' => $this->config['http_only'],
            'samesite' => $this->config['same_site']
        ]);

        session_name($this->config['cookie']);
        return session_start();
    }

    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function clear(): void
    {
        session_unset();
    }

    public function regenerate(): bool
    {
        return session_regenerate_id(true);
    }

    public function destroy(): bool
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
            return true;
        }
        return false;
    }

    public function getId(): string
    {
        return session_id();
    }

    public function all(): array
    {
        $data = [];
        foreach ($_SESSION as $key => $value) {
            $data[$key] = $value;
        }
        return $data;
    }
}
