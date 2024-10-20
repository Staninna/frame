<?php

namespace Frame\Router;

use Frame\Http\Method;
use Frame\Http\Request;
use Frame\Http\Response;

class Route
{
    public string $path;
    public array $params = [];
    public Method $method;
    /** @var callable|array */
    public $handler;
    public array $middlewares;
    public ?string $name;

    public function __construct(Method $method, string $path, $handler, ?string $name = null, array $middlewares = [])
    {
        $this->method = $method;
        $this->path = $path;
        $this->handler = $handler;
        $this->name = $name;
        $this->middlewares = $middlewares;

        $this->params = $this->getParams();
    }

    public function __invoke(Route $route, Request $request, Response $response): void
    {
        if (is_array($this->handler)) {
            [$controller, $method] = $this->handler;
            $controller = new $controller();
            $controller->$method($route, $request, $response);
        } elseif (is_callable($this->handler)) {
            ($this->handler)($route, $request, $response);
        } else {
            throw new \InvalidArgumentException("Invalid handler type");
        }
    }

    private function getParams(): array
    {
        $pathParts = explode('/', trim($this->path, '/'));

        $params = [];
        foreach ($pathParts as $index => $pathPart) {
            if (isset($pathPart[0]) && $pathPart[0] === ':') {
                $part = explode('/', trim($_SERVER['REQUEST_URI'], '/'))[$index];

                if (str_contains($part, '?')) {
                    $value = explode('?', $part)[0];
                    $params[substr($pathPart, 1)] = $value;
                    continue;
                }

                $params[substr($pathPart, 1)] = $part;
            }
        }

        return $params;
    }
}