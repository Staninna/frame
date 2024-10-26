<?php

namespace controllers;

use app\models\Task;
use app\models\User;
use Frame\Controller\BaseController;
use Frame\Http\Request;
use Frame\Http\Response;
use Frame\Router\Route;

class HomeController extends BaseController
{
    public function index(Route $route, Request $request, Response $response): void
    {
        $tasks = Task::all();
        $users = User::all();

        $this->view('home.index', compact('tasks', 'users'));
    }
}