<?php

use Frame\Controller\BaseController;
use Frame\Http\Request;
use Frame\Http\Response;

class UsersController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        // Validate request
//        $request->validate([
//            'name' => ['required', 'min:3', 'max:255'],
//            'email' => ['required', 'email'],
//            'age' => ['required', 'numeric', 'min:18', 'max:100'],
//            'password' => ['required', 'regex:/^[a-zA-Z0-9]{8,}$/'],
//        ]);
//
//        if ($request->getValidationErrors()) {
//            $response->write("Validation errors: " . json_encode($request->getValidationErrors()));
//            return;
//        }

//        $response->write("Request body: " . json_encode($request->body));

        $this->view('users/index', [
            'name' => 'stan',
            'email' => 'stan@example.com',
        ]);
    }
}