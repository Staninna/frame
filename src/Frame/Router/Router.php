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

    /**
     * Generate a URL for a named route with optional query parameters
     *
     * @param string $name The name of the route
     * @param array $queryParams Optional query parameters
     * @return string The generated URL
     * @throws Exception If the route name does not exist
     */
    public function url(string $name, array $queryParams = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new Exception("Route name '{$name}' does not exist.");
        }

        $url = $this->namedRoutes[$name]->path;

        if (!empty($queryParams)) {
            $queryString = http_build_query($queryParams);
            $url .= '?' . $queryString;
        }

        return $url;
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
            if ($route->method === $request->method && $this->matchPath($route->path, $request->path)) {
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
     * @param string $routePath
     * @param string $requestPath
     * @return bool
     */
    private function matchPath(string $routePath, string $requestPath): bool
    {
        $routeParts = explode('/', trim($routePath, '/'));
        $requestParts = explode('/', trim($requestPath, '/'));

        if (count($routeParts) !== count($requestParts)) {
            return false;
        }

        foreach ($routeParts as $index => $routePart) {
            if ($routePart !== $requestParts[$index]) {
                return false;
            }
        }

        return true;
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
