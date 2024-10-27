<?php

namespace Frame\Controller;

abstract class BaseController
{
    public function __construct()
    {
    }

    // TODO: Make config.php or smth to store the template directory among other things
    // TODO: Make a functions.php file in the framework for common functions
    public static function view(string $view, array $data = [], string $templateDir = 'views'): void
    {
        extract($data);
        include BASE_PATH . "/app/{$templateDir}/{$view}.php";
    }
}
