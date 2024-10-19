<?php

namespace Router;

class Request
{
    public Method $method;
    public string $path;
    public array $headers;
    public array $queryParams;
    public mixed $body;
    public array $params = [];

    public function __construct()
    {
        $this->method = Method::from($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $this->headers = $this->getRequestHeaders();
        $this->queryParams = $_GET ?? [];
        $this->body = $this->getRequestBody();
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
}
