<?php

namespace Router;

class Router
{
    /** @var array<array{method: string, path: string, handler: callable, middlewares: array<callable>}> */
    private array $routes = [];
    private string $prefix = '';
    /** @var array<callable> */
    private array $groupMiddlewares = [];

    // properties
    private int $maxHistory;

    public function __construct(int $maxHistory = 100)
    {
        $this->maxHistory = $maxHistory;
    }

    public function add(Method $method, string $path, callable $handler, array $middlewares = []): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $this->prefix . $path,
            'handler' => $handler,
            'middlewares' => array_merge($this->groupMiddlewares, $middlewares)
        ];
    }

    public function group(string $prefix, callable $callback, array $middlewares = []): void
    {
        $previousPrefix = $this->prefix;
        $previousMiddlewares = $this->groupMiddlewares;

        $this->prefix .= $prefix;
        $this->groupMiddlewares = array_merge($this->groupMiddlewares, $middlewares);

        $callback($this);

        $this->prefix = $previousPrefix;
        $this->groupMiddlewares = $previousMiddlewares;
    }

    public function run(Method $method, string $path): void
    {
        $this->addNavigationHistory($path, $method);

        foreach ($this->routes as $route) {
            $params = $this->matchPath($route['path'], $path);
            if ($route['method'] === $method && $params !== false) {
                $this->runMiddlewares($route['middlewares']);

                $body = $this->getRequestBody();

                // TODO: Handler should take params and body as arguments we might want to abstract this into a request class
                call_user_func($route['handler'], $params, $body);
                return;
            }
        }

        // TODO: Proper error handling also in route handlers
        http_response_code(404);
        echo "404 - Not Found";
    }

    /**
     * store user navigation history in session
     * @param string $path
     * @param Method $method
     */
    public function addNavigationHistory(string $path, Method $method): void
    {
        if (!isset($_SESSION['navigation_history'])) {
            $_SESSION['navigation_history'] = [];
        }

        while (count($_SESSION['navigation_history']) > $this->maxHistory) {
            array_shift($_SESSION['navigation_history']);
        }

        $_SESSION['navigation_history'][] = [
            'path' => $path,
            'method' => $method,
            'timestamp' => time()
        ];
    }

    /**
     * @return array<string, string>|false
     */
    private function matchPath(string $routePath, string $requestPath): array|false
    {
        $routeParts = explode('/', trim($routePath, '/'));
        $requestParts = explode('/', trim($requestPath, '/'));

        if (count($routeParts) !== count($requestParts)) {
            return false;
        }

        $params = [];
        foreach ($routeParts as $index => $routePart) {
            if ($routePart[0] === ':') {
                $params[substr($routePart, 1)] = $requestParts[$index];
            } elseif ($routePart !== $requestParts[$index]) {
                return false;
            }
        }

        return $params;
    }

    /**
     * @param array<callable> $middlewares
     */
    private function runMiddlewares(array $middlewares): void
    {
        foreach ($middlewares as $middleware) {
            $middleware();
        }
    }

    /**
     * @return array|string
     */
    private function getRequestBody(): array|string
    {
        $body = file_get_contents('php://input');
        $contentType = isset($_SERVER['CONTENT_TYPE']) ? trim($_SERVER['CONTENT_TYPE']) : '';

        if (strcasecmp($contentType, 'application/json') == 0) {
            $body = json_decode($body, true);
            return $body !== null ? $body : [];
        }

        return $body;
    }
}
