<?php

namespace Frame\Controller;

abstract class BaseController
{
    public function __construct()
    {
    }

    // TODO: Make config.php or smth to store the template directory among other things
    public static function view(string $view, array $data = [], string $templateDir = 'views'): void
    {
        extract($data);
        include __DIR__ . "/../../{$templateDir}/{$view}.php";
    }
}