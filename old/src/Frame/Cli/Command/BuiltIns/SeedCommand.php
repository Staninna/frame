<?php

namespace Frame\Cli\Command\BuiltIns;

use PDO;
use RuntimeException;
use Frame\Cli\Command\Command;

class SeedCommand extends Command
{
    private PDO $pdo;
    private string $seedersPath;

    public function __construct()
    {
        // TODO: Duped code
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $username = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: 'root';
        $dbname = getenv('DB_NAME') ?: 'test';
        $port = getenv('DB_PORT') ?: '3306';

        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $seedersPath = __DIR__ . '/../../../../../app/seeders';

        parent::__construct('seed', 'Seed the database with initial data');
        $this->pdo = $pdo;

        if (!realpath($seedersPath)) {
            echo "Seeders path not found: $seedersPath\n";
            echo "In the app directory, create a 'seeders' directory and put your seeder files in it.\n";
            exit(1);
        } else {
            $this->seedersPath = realpath($seedersPath);
        }
    }

    public function run($arguments): void
    {
        require_once __DIR__ . '/../../Db/Seeder.php';
        $availableSeeders = $this->getAvailableSeeders();

        if (empty($availableSeeders)) {
            echo "--------------------------------------------------------------------------------\n";
            echo "No seeders found in {$this->seedersPath}\n";
            echo "--------------------------------------------------------------------------------\n";
            return;
        }

        echo "--------------------------------------------------------------------------------\n";
        echo "Running " . count($availableSeeders) . " seeder(s)...\n";
        echo "--------------------------------------------------------------------------------\n";

        $failed = 0;
        foreach ($availableSeeders as $seeder) {
            echo "Running seeder: " . $seeder . "\n";
            $this->runSeeder($seeder, $failed);
        }

        echo "--------------------------------------------------------------------------------\n";
        echo "Completed " . (count($availableSeeders) - $failed) . " seeder(s) successfully.\n";
        if ($failed > 0) {
            echo "Failed to run $failed seeder(s).\n";
        }
        echo "--------------------------------------------------------------------------------\n";
    }

    private function getAvailableSeeders(): array
    {
        $files = scandir($this->seedersPath);
        return array_filter($files, function ($file) {
            return preg_match('/^\d+_.*Seeder\.php$/', $file);
        });
    }

    private function runSeeder(string $seeder, int &$failed): void
    {
        require_once $this->seedersPath . '/' . $seeder;

        $className = pathinfo($seeder, PATHINFO_FILENAME);
        $className = substr(strstr($className, '_'), 1);
        $fullClassName = "app\\seeders\\$className";

        if (!class_exists($fullClassName)) {
            throw new RuntimeException("Seeder class '$fullClassName' not found in file '$seeder'");
        }

        $instance = new $fullClassName();

        $this->pdo->beginTransaction();

        try {
            if (!method_exists($instance, 'run')) {
                throw new RuntimeException("Run method not found in seeder $seeder");
            }

            $instance->run($this->pdo);

            if ($this->pdo->inTransaction()) {
                $this->pdo->commit();
            }
            echo "Completed seeder: $seeder\n";
        } catch (\Exception $e) {
            echo "Failed to run seeder $seeder: " . $e->getMessage() . "\n";
            $failed++;
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            exit(1);
        }
    }
}