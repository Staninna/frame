<?php

namespace Frame\Boot;

use ErrorException;
use Frame\Http\Request;
use Frame\Http\Response;
use Frame\Router\Router;
use Frame\Session\SessionManager;
use PDO;

class Bootstrap
{
    private Router $router;
    private array $config = [];
    private SessionManager $session; // TODO: Make this a service

    /**
     * @throws ErrorException
     */
    public function __construct()
    {
        // Load environment variables
        $this->loadEnvironmentVariables();

        // Load configuration
        $this->loadConfiguration();

        // Initialize core components
        $this->session = new SessionManager($this->config['session']);
        $this->session->start();
        $this->initializeDatabase();
        $this->initializeRouter();

        // Register error handlers
        $this->registerErrorHandlers();
    }

    private function loadEnvironmentVariables(): void
    {
        $envFile = BASE_PATH . '/.env';

        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (str_contains($line, '=') && !str_starts_with($line, '#')) {
                    list($name, $value) = explode('=', $line, 2);
                    $name = trim($name);
                    $value = trim($value);

                    if (!empty($name)) {
                        putenv(sprintf('%s=%s', $name, $value));
                        $_ENV[$name] = $value;
                        $_SERVER[$name] = $value;
                    }
                }
            }
        }
    }

    private function loadConfiguration(): void
    {
        // Load configuration files from app/config
        $configPath = BASE_PATH . '/app/config/';

        $files = scandir($configPath);
        foreach ($files as $file) {
            $namespace = pathinfo($file, PATHINFO_FILENAME);
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $this->config[$namespace] = require $configPath . $file;
            }
        }
    }

    private function initializeDatabase(): void
    {
        $host = $this->config['database']['connections']['mysql']['host']; // TODO: Remove all $this->config[...] and replace them with a config() function at some point
        $port = $this->config['database']['connections']['mysql']['port'];
        $username = $this->config['database']['connections']['mysql']['username'];
        $password = $this->config['database']['connections']['mysql']['password'];
        $dbname = $this->config['database']['connections']['mysql']['database'];

        $database = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
        $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Detect models and set the database connection for them
        $modelsPath = BASE_PATH . '/app/models';
        $files = scandir($modelsPath);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php' && pathinfo($file, PATHINFO_FILENAME) !== 'Model') {
                require_once $modelsPath . '/' . $file;
                $className = pathinfo($file, PATHINFO_FILENAME);
                $fullClassName = "app\\models\\$className";
                $model = new $fullClassName();
                $model->setDatabaseConnection($database);
            }
        }
    }

    private function initializeRouter(): void
    {
        $this->router = new Router();

        // Register routes
        $router = $this->router;
        require BASE_PATH . '/app/routes.php';
    }

    private function registerErrorHandlers(): void
    {
        error_reporting(E_ALL);

        set_error_handler(
        /**
         * @throws ErrorException
         */
            function ($severity, $message, $file, $line) {
                if (!(error_reporting() & $severity)) {
                    return false;
                }
                throw new ErrorException($message, 0, $severity, $file, $line);
            });

        set_exception_handler(function (\Throwable $e) {
            $this->handleException($e);
        });
    }

    private
    function handleException(\Throwable $e): void
    {
        if ($this->config['app']['debug'] ?? false) {
            // Show detailed error information in debug mode
            echo '<h1>Error</h1>';
            echo '<p>Message: ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p>File: ' . htmlspecialchars($e->getFile()) . '</p>';
            echo '<p>Line: ' . htmlspecialchars($e->getLine()) . '</p>';
            echo '<h2>Stack Trace:</h2>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            // phpstorm URL to see source code
            // TODO: Make line work
            $file = str_replace(BASE_PATH . '/', '', $e->getFile());
            echo '<p><a href="jetbrains://php-storm/navigate/reference?project=notag&path=' . urlencode($file) . '&line=' . $e->getLine() . '">Open in PHPStorm</a></p>';
        } else {
            // Show generic error in production
            http_response_code(500);
            echo 'An internal server error occurred.';
        }

        // Log the error
        $logMessage = sprintf(
            "[%s] %s: %s in %s on line %d\n",
            date('Y-m-d H:i:s'),
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );

         error_log($logMessage, 3, BASE_PATH . '/storage/logs/error.log');
    }

    public
    function run(): void
    {
        try {
            // Create request and response objects
//            $request = new Request();
//            $response = new Response();

            // Run the router
            $this->router->run($request, $response);

        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }
}
