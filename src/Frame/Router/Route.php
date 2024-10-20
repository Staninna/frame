<?php

namespace Frame\Router;

use Frame\Http\Method;
use Frame\Http\Request;
use Frame\Http\Response;

class Route
{
    public string $path;
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
    }

    public function __invoke(Request $request, Response $response): void
    {
        if (is_array($this->handler)) {
            [$controller, $method] = $this->handler;
            $controller = new $controller();
            $controller->$method($request, $response);
        } elseif (is_callable($this->handler)) {
            ($this->handler)($request, $response);
        } else {
            throw new \InvalidArgumentException("Invalid handler type");
        }
    }
}