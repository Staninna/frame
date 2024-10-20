<?php

namespace Frame\Router;

use Exception;
use Frame\Http\Method;
use Frame\Http\Request;
use Frame\Http\Response;

class Router
{
    /** @var Route[] */
    private array $routes = [];
    private string $prefix = '';
    /** @var array<callable> */
    private array $groupMiddlewares = [];
    /** @var Route[] */
    private array $namedRoutes = [];
    private int $maxHistory;

    public function __construct(int $maxHistory = 100)
    {
        $this->maxHistory = $maxHistory;
    }

    /**
     * @throws Exception
     */
    public function get(string $path, callable|array $handler, string $name = null, array $middlewares = []): void
    {
        $this->add(Method::GET, $path, $handler, $name, $middlewares);
    }

    /**
     * @throws Exception
     */
    public function post(string $path, callable|array $handler, string $name = null, array $middlewares = []): void
    {
        $this->add(Method::POST, $path, $handler, $name, $middlewares);
    }

    /**
     * @throws Exception
     */
    public function put(string $path, callable|array $handler, string $name = null, array $middlewares = []): void
    {
        $this->add(Method::PUT, $path, $handler, $name, $middlewares);
    }

    /**
     * @throws Exception
     */
    public function delete(string $path, callable|array $handler, string $name = null, array $middlewares = []): void
    {
        $this->add(Method::DELETE, $path, $handler, $name, $middlewares);
    }

    /**
     * @throws Exception
     */
    public function patch(string $path, callable|array $handler, string $name = null, array $middlewares = []): void
    {
        $this->add(Method::PATCH, $path, $handler, $name, $middlewares);
    }

    /**
     * @throws Exception
     */
    private function add(Method $method, string $path, callable|array $handler, string $name = null, array $middlewares = []): void
    {
        $route = new Route(
            $method,
            $this->prefix . $path,
            $handler,
            $name,
            array_merge($this->groupMiddlewares, $middlewares)
        );

        $this->routes[] = $route;
        if ($name !== null) {
            if (isset($this->namedRoutes[$name])) {
                throw new Exception("Route name '{$name}' is already in use.");
            }
            $this->namedRoutes[$name] = $route;
        }
    }

    // TODO: Add support for query parameters

    /**
     * @throws Exception
     */
    public function url(string $name): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new Exception("Route name '{$name}' does not exist.");
        }

        return $this->namedRoutes[$name]['path'];
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
        if (!session_id()) {
            session_start();
        }

        $request = new Request();
        $response = new Response();

        $this->addNavigationHistory($request->path, $request->method);

        foreach ($this->routes as $route) {
            $params = $this->matchPath($route->path, $request->path);
            if ($route->method === $request->method && $params !== false) {
                $request->params = $params;

                $this->runMiddlewaresAndHandler($route, $request, $response);
                return;
            }
        }

        // TODO: Proper error handling
        $response->setStatusCode(404);
        $response->write("404 - Not Found");
        $response->send();
    }

    /**
     * Store user navigation history in session
     * @param string $path Path
     * @param Method $method HTTP method
     */
    private function addNavigationHistory(string $path, Method $method): void
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
     * @param Route $route
     * @param Request $request
     * @param Response $response
     */
    private function runMiddlewaresAndHandler(Route $route, Request $request, Response $response): void
    {
        $middlewareChain = array_reduce(
            array_reverse($route->middlewares),
            function ($next, $middleware) {
                return function (Request $request, Response $response) use ($middleware, $next) {
                    $middleware($request, $response, $next);
                };
            },
            function (Request $request, Response $response) use ($route) {
                $route($request, $response);
            }
        );

        $middlewareChain($request, $response);
        $response->send();
    }
}
