<?php

namespace Frame\Session;

class SessionManager
{
    private SessionDriverInterface $driver;
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'driver' => 'native',
            'lifetime' => 120,
            'expire_on_close' => false,
            'secure' => true,
            'http_only' => true,
            'same_site' => 'lax',
            'path' => '/',
            'domain' => null,
        ], $config);

        $this->driver = $this->createDriver();
    }

    private function createDriver(): SessionDriverInterface
    {
        return match($this->config['driver']) {
            'native' => new NativeSessionDriver($this->config),
            'file' => new FileSessionDriver($this->config),
//            'database' => new DatabaseSessionDriver($this->config), // TODO: Make this work
//            'redis' => new RedisSessionDriver($this->config),       // TODO: Make this work
            default => throw new \InvalidArgumentException("Unsupported session driver: {$this->config['driver']}")
        };
    }

    public function start(): bool
    {
        if ($this->driver->start()) {
            $this->setupSecurityHeaders();
            return true;
        }
        return false;
    }

    private function setupSecurityHeaders(): void
    {
        // Set security headers
        header_remove('X-Powered-By'); // Remove the X-Powered-By header
        header('X-Frame-Options: DENY'); // Prevent clickjacking (i-framing)
        header('X-Content-Type-Options: nosniff'); // Prevent MIME type sniffing
        header('X-XSS-Protection: 1; mode=block'); // Prevent XSS attacks

        if ($this->config['secure']) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains'); // Enforce HTTPS and HSTS
        }
    }

    public function get(string $key, $default = null)
    {
        return $this->driver->get($key, $default);
    }

    public function set(string $key, $value): void
    {
        $this->driver->set($key, $value);
    }

    public function flash(string $key, $value): void
    {
        $this->set('_flash.' . $key, $value);
    }

    public function has(string $key): bool
    {
        return $this->driver->has($key);
    }

    public function remove(string $key): void
    {
        $this->driver->remove($key);
    }

    public function clear(): void
    {
        $this->driver->clear();
    }

    public function regenerate(): bool
    {
        return $this->driver->regenerate();
    }

    public function destroy(): bool
    {
        return $this->driver->destroy();
    }

    public function token(): string
    {
        if (!$this->has('_token')) {
            $this->set('_token', bin2hex(random_bytes(32)));
        }
        return $this->get('_token');
    }
}

// Middleware for Session Handling
class SessionMiddleware
{
    private SessionManager $session;

    public function __construct(SessionManager $session)
    {
        $this->session = $session;
    }

    public function handle($request, $next)
    {
        $this->session->start();

        // Process flash messages
        $this->removeExpiredFlashData();

        // Verify CSRF token for POST requests
        if ($request->method === 'POST') {
            $this->validateCsrfToken($request);
        }

        $response = $next($request);

        // Save session data
        $this->session->save();

        return $response;
    }

    private function removeExpiredFlashData(): void
    {
        $flash = $this->session->get('_flash', []);

        foreach ($flash as $key => $value) {
            $this->session->remove('_flash.' . $key);
        }
    }

    private function validateCsrfToken($request): void
    {
        $token = $request->input('_token');

        if (!$token || $token !== $this->session->token()) {
            throw new \RuntimeException('CSRF token mismatch');
        }
    }
}
