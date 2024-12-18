<?php

namespace Frame\Http;

use Frame\Utils\Sanitizer;
use Frame\Validation\Validator;

class Request
{
    public Method $method;
    public string $path;
    public array $headers;
    public mixed $body;
    public array $queryParams;

    private Validator $validator;
    private Sanitizer $sanitizer;

    public function __construct()
    {
        $this->method = Method::from($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $this->headers = $this->getRequestHeaders();
        $this->queryParams = $this->getQueryParams();
        $this->body = $this->getRequestBody();

        $this->validator = new Validator();
        $this->sanitizer = new Sanitizer();
    }

    public function validate(array $rules): bool
    {
        return $this->validator->validate($this->body, $rules);
    }

    public function sanitize(array $rules): array
    {
        return $this->sanitizer->sanitize($this->body, $rules);
    }

    public function getValidationErrors(): array
    {
        return $this->validator->getErrors();
    }

    private function getRequestHeaders(): array
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        // Fallback for servers that don't support getallheaders()
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (str_starts_with($name, 'HTTP_')) {
                $headerName = str_replace('_', '-', strtolower(substr($name, 5)));
                $headers[$headerName] = $value;
            }
        }
        return $headers;
    }

    private function getRequestBody(): mixed
    {
        $body = file_get_contents('php://input');
        $contentType = $this->headers['content-type'] ?? '';

        if (stripos($contentType, 'application/json') !== false) {
            $body = json_decode($body, true);
            return $body !== null ? $body : [];
        }

        // Parse form data
        if (stripos($contentType, 'application/x-www-form-urlencoded') !== false) {
            parse_str($body, $parsedBody);
            return $parsedBody;
        }

        return $body;
    }

    private function getQueryParams(): array
    {
        $params = [];

        if (isset($_GET)) {
            $params = array_merge($params, $_GET);
        }

        if (isset($_POST)) {
            $params = array_merge($params, $_POST);
        }

        return $params;
    }
}
