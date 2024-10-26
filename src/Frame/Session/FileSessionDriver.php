<?php

namespace Frame\Session;

class FileSessionDriver implements SessionDriverInterface
{
    private array $config;
    private string $path;
    private array $data = [];
    private string $id;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->path = $config['files'] ?? storage_path('frame/sessions');
        $this->ensureDirectoryExists();
    }

    private function ensureDirectoryExists(): void
    {
        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }
    }

    public function start(): bool
    {
        $this->id = $_COOKIE[$this->config['cookie']] ?? $this->generateId();

        if ($this->loadSession()) {
            // Set session cookie
            setcookie(
                $this->config['cookie'],
                $this->id,
                [
                    'expires' => time() + ($this->config['lifetime'] * 60),
                    'path' => $this->config['path'],
                    'domain' => $this->config['domain'],
                    'secure' => $this->config['secure'],
                    'httponly' => $this->config['http_only'],
                    'samesite' => $this->config['same_site']
                ]
            );

            $this->save();
            return true;
        }

        return false;
    }

    private function loadSession(): bool
    {
        $file = $this->path . '/' . $this->id . '.session';

        if (file_exists($file)) {
            $content = file_get_contents($file);
            $data = unserialize($content);

            if ($data !== false) {
                $this->data = $data;
                return true;
            }
        }

        $this->data = [];
        return true;
    }

    private function generateId(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
        $this->save();
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function remove(string $key): void
    {
        unset($this->data[$key]);
        $this->save();
    }

    public function clear(): void
    {
        $this->data = [];
        $this->save();
    }

    public function regenerate(): bool
    {
        $oldFile = $this->path . '/' . $this->id;
        $this->id = $this->generateId();
        $newFile = $this->path . '/' . $this->id;

        if (file_exists($oldFile)) {
            rename($oldFile, $newFile);
        }

        return true;
    }

    public function destroy(): bool
    {
        $file = $this->path . '/' . $this->id;

        if (file_exists($file)) {
            unlink($file);
        }

        $this->data = [];
        return true;
    }

    public function getId(): string
    {
        return $this->id;
    }

    private function save(): void
    {
        $file = $this->path . '/' . $this->id . '.session';
        file_put_contents($file, serialize($this->data));
    }

    public function all(): array
    {
        return $this->data;
    }
}
