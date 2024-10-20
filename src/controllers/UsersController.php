<?php

namespace controllers;

use Frame\Controller\BaseController;
use Frame\Http\Request;
use Frame\Http\Response;
use Frame\Router\Route;

class UsersController extends BaseController
{
    public function index(Route $route, Request $request, Response $response): void
    {
        $name = $request->queryParams['name'] ?? 'world';
        $email = $request->queryParams['email'] ?? 'world@example.com';

        $this->view('users.index', compact('name', 'email'));
    }
}