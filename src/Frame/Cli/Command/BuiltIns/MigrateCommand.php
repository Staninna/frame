<?php

namespace Frame\Cli\Command\BuiltIns;

use Frame\Cli\Command\Command;
use PDO;

class MigrateCommand extends Command
{
    private PDO $pdo;
    private string $migrationsPath;

    public function __construct()
    {
        // TODO: Duped code
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $username = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: 'root';
        $dbname = getenv('DB_NAME') ?: 'test';
        $port = getenv('DB_PORT') ?: '3306';

        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password); // TODO: Get connection from config

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $migrationsPath = __DIR__ . '/../../../../migrations'; // TODO: Get migrations path from config

        parent::__construct('migrate', 'Run database migrations');
        $this->pdo = $pdo;

        if (!realpath($migrationsPath)) {
            echo "Migrations path not found: $migrationsPath\n";
            echo "Next to the Frame directory, create a migrations directory and put your migration files in it.\n";
            exit(1);
        } else {
            $this->migrationsPath = realpath($migrationsPath);
        }
    }

    public function run($arguments): void
    {
        $this->createMigrationsTableIfNotExists();
        $appliedMigrations = $this->getAppliedMigrations();
        $availableMigrations = $this->getAvailableMigrations();

        $migrationsToApply = array_diff($availableMigrations, $appliedMigrations);

        if (empty($migrationsToApply)) {
            echo "No new migrations to apply.\n";
            return;
        }

        foreach ($migrationsToApply as $migration) {
            $this->applyMigration($migration);
        }

        echo "All migrations applied successfully.\n";
    }

    private function createMigrationsTableIfNotExists(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255),
                applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    private function getAppliedMigrations(): array
    {
        $stmt = $this->pdo->query("SELECT migration FROM migrations");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function getAvailableMigrations(): array
    {
        $files = scandir($this->migrationsPath);
        return array_filter($files, function ($file) {
            return preg_match('/^\d+_.*\.php$/', $file);
        });
    }

    private function applyMigration(string $migration): void
    {
        // TODO: Figure out how this works seems to look fine didnt test yet
        require_once $this->migrationsPath . '/' . $migration;

        $className = 'Migration_' . pathinfo($migration, PATHINFO_FILENAME);
        $instance = new $className();

        echo "Applying migration: $className\n";
        echo "Migration file: $migration\n";

        $this->pdo->beginTransaction();

        try {
            $instance->up($this->pdo);
            $this->pdo->exec("INSERT INTO migrations (migration) VALUES (:migration)");
            $stmt = $this->pdo->prepare("INSERT INTO migrations (migration) VALUES (:migration)");
            $stmt->execute(['migration' => $migration]);
            $this->pdo->commit();
            echo "Applied migration: $migration\n";
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            echo "Failed to apply migration $migration: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}