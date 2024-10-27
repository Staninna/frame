<?php

namespace Frame\Core\Http;

class Uri
{
    private string $scheme = '';
    private string $username = '';
    private string $password = '';
    private string $host = '';
    private ?int $port = null;
    private string $path = '';
    private string $query = '';
    private string $fragment = '';

    public function __construct(string $uri, ?array $server = null)
    {
        // Get server variables (either from $_SERVER or mock data)
        $server = $server ?? $_SERVER;

        // Parse the provided URI first
        $parts = parse_url($uri);

        // Set path and query from the parsed URI
        $this->path = $parts['path'] ?? '/';
        $this->query = $parts['query'] ?? '';

        // If we're in a web context or have mock data, fill in the rest
        if (isset($server['HTTP_HOST'])) {
            // Split host and port if provided
            $hostParts = explode(':', $server['HTTP_HOST']);
            $this->host = $hostParts[0];
            if (isset($hostParts[1])) {
                $this->port = (int)$hostParts[1];
            }
        }

        // Determine scheme
        if (isset($server['HTTPS']) && $server['HTTPS'] !== 'off' ||
            isset($server['REQUEST_SCHEME']) && $server['REQUEST_SCHEME'] === 'https' ||
            isset($server['HTTP_X_FORWARDED_PROTO']) && $server['HTTP_X_FORWARDED_PROTO'] === 'https') {
            $this->scheme = 'https';
        } else {
            $this->scheme = 'http';
        }

        // Set standard ports if not explicitly set
        if ($this->port === null) {
            $this->port = $this->scheme === 'https' ? 443 : 80;
        }

        // Set authorization if present
        if (isset($server['PHP_AUTH_USER'])) {
            $this->username = $server['PHP_AUTH_USER'];
            $this->password = $server['PHP_AUTH_PW'] ?? '';
        }
    }

    // Getters
    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    public function getQueryParams(): array
    {
        if (empty($this->query)) {
            return [];
        }
        parse_str($this->query, $params);
        return $params;
    }

    public function __toString(): string
    {
        $uri = '';

        // Add scheme if present
        if ($this->scheme !== '') {
            $uri .= $this->scheme . '://';
        }

        // Add authentication if present
        if ($this->username !== '') {
            $uri .= $this->username;
            if ($this->password !== '') {
                $uri .= ':' . $this->password;
            }
            $uri .= '@';
        }

        // Add host
        $uri .= $this->host;

        // Add port if non-standard
        if ($this->port !== null &&
            !(($this->scheme === 'http' && $this->port === 80) ||
                ($this->scheme === 'https' && $this->port === 443))) {
            $uri .= ':' . $this->port;
        }

        // Add path
        $uri .= $this->path;

        // Add query if present
        if ($this->query !== '') {
            $uri .= '?' . $this->query;
        }

        // Add fragment if present
        if ($this->fragment !== '') {
            $uri .= '#' . $this->fragment;
        }

        return $uri;
    }
}