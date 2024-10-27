<?php

namespace Frame\Core\Http;

class Request extends Message implements HttpConstants
{
    protected Method $method;
    protected Uri $uri;
    protected array $serverParams;
    protected array $queryParams;
    protected array $parsedBody;

    public function __construct()
    {
        // TODO: Dirty way for testing
        if (isset($_SERVER['REQUEST_MOCKED']) && $_SERVER['REQUEST_MOCKED'] === 'true') {
            return;
        }

        $this->initialize();
    }

    protected function initialize(): void
    {
        $this->serverParams = $_SERVER;
        $this->method = Method::fromString($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->uri = new Uri($_SERVER['REQUEST_URI'] ?? '/');
        $this->queryParams = $_GET;
        $this->headers = $this->getRequestHeaders();
        $this->parsedBody = $this->parseRequestBody();
    }

    /**
     * Mocks the request with the given data
     *
     * @param array $data The data to mock the request with
     *
     * # Example
     * ```php
     * $request = new Request();
     * $request->mock([
     *
     * ]);
     * ```
     */
    public function mock(array $data): void
    {
        // Create server params for mocking
        $serverParams = [
            'REQUEST_METHOD' => $data['REQUEST_METHOD'] ?? 'GET',
            'REQUEST_URI' => $data['REQUEST_URI'] ?? '/',
            'HTTP_HOST' => $data['HTTP_HOST'] ?? 'localhost',
            'HTTPS' => $data['HTTPS'] ?? 'off',
            'SERVER_PORT' => $data['SERVER_PORT'] ?? '80',
        ];

        // Add any HTTP_* headers to server params
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $serverParams[$key] = $value;
            }
        }

        // Set server params
        $this->serverParams = array_merge($_SERVER, $serverParams);

        // Set method
        $this->method = Method::fromString($serverParams['REQUEST_METHOD']);

        // Set URI with server params for full URI construction
        $this->uri = new Uri($serverParams['REQUEST_URI'], $serverParams);

        // Set headers
        $this->headers = [];
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerName = str_replace('_', '-', substr($key, 5));
                $this->headers[strtolower($headerName)] = [$value];
            }
        }

        // Set query parameters and parsed body
        $this->queryParams = $data['_GET'] ?? [];
        $this->parsedBody = $data['_POST'] ?? [];
    }

    protected function getRequestHeaders(): array
    {
        if (function_exists('getallheaders')) {
            return array_change_key_case(getallheaders());
        }

        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (str_starts_with($name, 'HTTP_')) {
                $name = str_replace('_', '-', strtolower(substr($name, 5)));
                $headers[$name] = [$value];
            }
        }
        return $headers;
    }

    protected function parseRequestBody(): array
    {
        $contentType = $this->getHeaderLine(HttpConstants::HEADER_CONTENT_TYPE);
        $body = file_get_contents('php://input');

        if (str_contains($contentType, HttpConstants::CONTENT_TYPE_JSON)) {
            $data = json_decode($body, true);
            return is_array($data) ? $data : [];
        }

        if (str_contains($contentType, HttpConstants::CONTENT_TYPE_FORM)) {
            parse_str($body, $data);
            return $data;
        }

        // TODO: Parse multipart/form-data

        return $_POST;
    }

    public function getMethod(): Method
    {
        return $this->method;
    }

    public function getUri(): Uri
    {
        return $this->uri;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function getParsedBody(): array
    {
        return $this->parsedBody;
    }

    public function getServerParams(): array
    {
        return $this->serverParams;
    }
}