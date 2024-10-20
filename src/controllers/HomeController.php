<?php

namespace controllers;

use Frame\Controller\BaseController;
use Frame\Http\Request;
use Frame\Http\Response;
use Frame\Router\Route;

class HomeController extends BaseController
{
    public function index(Route $route, Request $request, Response $response): void
    {
        $this->view('home.index');
    }
}