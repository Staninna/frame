<?php declare(strict_types=1);

namespace Tests;

require __DIR__ . '/require.php';

use Frame\Core\Http\HttpConstants;
use Frame\Core\Http\Method;
use Frame\Core\Http\Request;

// Enable mocking and debugging
const MOCKING = true;
const DEBUG = true;

function debugRequest(Request $request): void
{
    echo "\n=== REQUEST DEBUG ===\n";
    echo "Method:     " . $request->getMethod()->value . "\n";
    echo "Full URI:   " . $request->getUri() . "\n";
    echo "Scheme:     " . $request->getUri()->getScheme() . "\n";
    echo "Host:       " . $request->getUri()->getHost() . "\n";
    echo "Port:       " . $request->getUri()->getPort() . "\n";
    echo "Path:       " . $request->getUri()->getPath() . "\n";
    echo "Query:      " . $request->getUri()->getQuery() . "\n";

    echo "\nHeaders:\n";
    foreach ($request->getHeaders() as $name => $values) {
        echo "  $name: " . implode(', ', $values) . "\n";
    }

    echo "\nQuery Parameters:\n";
    foreach ($request->getQueryParams() as $key => $value) {
        echo "  $key: $value\n";
    }

    echo "\nParsed Body:\n";
    foreach ($request->getParsedBody() as $key => $value) {
        if (is_array($value)) {
            echo "  $key: " . json_encode($value) . "\n";
        } else {
            echo "  $key: $value\n";
        }
    }
    echo "==================\n\n";
}

// Test various request scenarios
$testCases = [
    // Test Case 1: Simple GET request
    [
        'name' => 'Simple GET Request',
        'mock' => [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/users',
            'HTTP_HOST' => 'api.example.com',
            'HTTP_ACCEPT' => 'application/json',
            '_GET' => ['page' => '1', 'limit' => '10'],
        ]
    ],

    // Test Case 2: HTTPS POST request with JSON body
    [
        'name' => 'HTTPS POST with JSON',
        'mock' => [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => '/api/v1/users',
            'HTTP_HOST' => 'secure.example.com',
            'HTTPS' => 'on',
            'SERVER_PORT' => '443',
            'HTTP_CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer abc123',
            '_POST' => [
                'user' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'roles' => ['admin', 'user']
                ]
            ]
        ]
    ],

    // Test Case 3: Custom port with query parameters
    [
        'name' => 'Custom Port with Query Params',
        'mock' => [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/search?q=test&filter=active',
            'HTTP_HOST' => 'localhost',
            'SERVER_PORT' => '8080',
            'HTTP_ACCEPT' => 'application/json',
            '_GET' => ['q' => 'test', 'filter' => 'active']
        ]
    ],

    // Test Case 4: PUT request with form data
    [
        'name' => 'PUT with Form Data',
        'mock' => [
            'REQUEST_METHOD' => 'PUT',
            'REQUEST_URI' => '/api/v1/users/123',
            'HTTP_HOST' => 'api.example.com',
            'HTTP_CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'HTTP_X_API_KEY' => 'xyz789',
            '_POST' => [
                'status' => 'active',
                'role' => 'admin'
            ]
        ]
    ],

    // Test Case 5: Complex path with multiple query parameters
    [
        'name' => 'Complex Path with Query Parameters',
        'mock' => [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/api/v2/organizations/123/departments/456/employees?sort=name&order=desc&include=roles,permissions',
            'HTTP_HOST' => 'api.example.com',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            '_GET' => [
                'sort' => 'name',
                'order' => 'desc',
                'include' => 'roles,permissions'
            ]
        ]
    ],

    // Test Case 6: DELETE request with authorization
    [
        'name' => 'DELETE with Authorization',
        'mock' => [
            'REQUEST_METHOD' => 'DELETE',
            'REQUEST_URI' => '/api/v1/posts/789',
            'HTTP_HOST' => 'api.example.com',
            'HTTPS' => 'on',
            'HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('username:password'),
            'PHP_AUTH_USER' => 'username',
            'PHP_AUTH_PW' => 'password'
        ]
    ]
];

// Run all test cases
foreach ($testCases as $testCase) {
    echo "\nTesting: " . $testCase['name'] . "\n";
    echo str_repeat('-', strlen($testCase['name']) + 9) . "\n";

    // Set mock status
    // TODO: Dirty way for testing
    $_SERVER['REQUEST_MOCKED'] = 'true';

    $request = new Request();
    $request->mock($testCase['mock']);
    debugRequest($request);

    // Wait for user input
    echo "Press enter to continue...\n";
    fgets(STDIN);
}

// Example usage in application code:
function handleUserCreation(Request $request): array
{
    // Validate method
    if (!$request->getMethod()->equals(Method::POST)) {
        return ['error' => 'Method not allowed'];
    }

    // Check content type
    if ($request->getHeaderLine(HttpConstants::HEADER_CONTENT_TYPE) !== HttpConstants::CONTENT_TYPE_JSON) {
        return ['error' => 'Content type must be application/json'];
    }

    // Get request data
    $userData = $request->getParsedBody();

    // Validate required fields
    if (empty($userData['user']['name']) || empty($userData['user']['email'])) {
        return ['error' => 'Name and email are required'];
    }

    // Process the request...
    return [
        'success' => true,
        'message' => 'User created successfully',
        'data' => $userData
    ];
}

// Test the handler
echo "\nTesting User Creation Handler\n";
echo "---------------------------\n";
$request = new Request();
$request->mock([
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI' => '/api/v1/users',
    'HTTP_HOST' => 'api.example.com',
    'HTTP_CONTENT_TYPE' => 'application/json',
    'HTTP_AUTHORIZATION' => 'Bearer xyz789',
    '_POST' => [
        'user' => [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'roles' => ['editor']
        ]
    ]
]);

$result = handleUserCreation($request);
echo "Handler Result:\n";
echo json_encode($result, JSON_PRETTY_PRINT) . "\n";