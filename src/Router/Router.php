<?php

namespace Router;

class Router
{
    /** @var array<array{method: string, path: string, handler: callable, middlewares: array<callable>}> */
    private array $routes = [];
    private string $prefix = '';
    /** @var array<callable> */
    private array $groupMiddlewares = [];

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

    public function run(): void
    {
        session_start();

        $request = new Request();
        $response = new Response();

        $this->addNavigationHistory($request->path, $request->method);

        foreach ($this->routes as $route) {
            $params = $this->matchPath($route['path'], $request->path);
            if ($route['method'] === $request->method && $params !== false) {
                $request->params = $params;

                $this->runMiddlewaresAndHandler($route['middlewares'], $route['handler'], $request, $response);
                return;
            }
        }

        // Proper error handling
        $response->setStatusCode(404);
        $response->write("404 - Not Found");
        $response->send();
    }

    /**
     * Store user navigation history in session
     * @param string $path Path
     * @param Method $method HTTP method
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
            if (isset($routePart[0]) && $routePart[0] === ':') {
                $params[substr($routePart, 1)] = $requestParts[$index];
            } elseif ($routePart !== $requestParts[$index]) {
                return false;
            }
        }

        return $params;
    }

    /**
     * Run middlewares and handler in a chain
     * @param array<callable> $middlewares
     * @param callable $handler
     * @param Request $request
     * @param Response $response
     */
    private function runMiddlewaresAndHandler(array $middlewares, callable $handler, Request $request, Response $response): void
    {
        $middlewareChain = array_reduce(
            array_reverse($middlewares),
            function ($next, $middleware) {
                return function (Request $request, Response $response) use ($middleware, $next) {
                    $middleware($request, $response, $next);
                };
            },
            function (Request $request, Response $response) use ($handler) {
                $handler($request, $response);
            }
        );

        $middlewareChain($request, $response);
        $response->send();
    }
}
