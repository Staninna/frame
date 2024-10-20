<?php

namespace controllers;

use Frame\Controller\BaseController;
use Frame\Http\Request;
use Frame\Http\Response;
use Frame\Router\Route;

use models\Task;

class HomeController extends BaseController
{
    public function index(Route $route, Request $request, Response $response): void
    {
        $tasks = Task::all();

        echo "<pre>";
        print_r($tasks);
        echo "</pre>";

        $this->view('home.index');
    }
}