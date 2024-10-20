<?php

use Frame\Controller\BaseController;
use Frame\Http\Request;
use Frame\Http\Response;

class UsersController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $name = $request->queryParams['name'] ?? 'world';
        $email = $request->queryParams['email'] ?? 'world@example.com';

        $this->view('users/index', compact('name', 'email'));
    }
}