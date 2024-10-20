<?php

namespace Frame\Router;

use Frame\Http\Method;
use Frame\Http\Request;
use Frame\Http\Response;

class Route
{
    public string $path;
    public Method $method;
    /**
     * @var callable|array
     * @method handler(Request $request, Response $response)
     */
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
}
