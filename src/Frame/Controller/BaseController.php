<?php

namespace Frame\Controller;

abstract class BaseController
{
    public function __construct()
    {
    }

    public static function view(string $view, array $data = []): void
    {
        extract($data);
        include __DIR__ . "/../../views/{$view}.php";
    }
}