<?php

namespace Frame\Cli\Command\BuiltIns;

use Frame\Cli\Command\Command;
use PDO;

class RollbackCommand extends Command
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

        parent::__construct('rollback', 'Rollback database migrations');
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
        $steps = isset($arguments[0]) ? (int)$arguments[0] : 1;

        $appliedMigrations = $this->getAppliedMigrations();

        if (empty($appliedMigrations)) {
            echo "No migrations to rollback.\n";
            return;
        }

        $migrationsToRollback = array_slice($appliedMigrations, -$steps);

        foreach (array_reverse($migrationsToRollback) as $migration) {
            $this->rollbackMigration($migration);
        }

        echo "Rollback completed successfully.\n";
    }

    private function getAppliedMigrations(): array
    {
        $stmt = $this->pdo->query("SELECT migration FROM migrations ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // TODO: Figure out how this works seems to look fine didnt test yet
    private function rollbackMigration(string $migration): void
    {
        require_once $this->migrationsPath . '/' . $migration;

        $className = 'Migration_' . pathinfo($migration, PATHINFO_FILENAME);
        $instance = new $className();

        $this->pdo->beginTransaction();

        try {
            if (method_exists($instance, 'down')) {
                $instance->down($this->pdo);
            } else {
                throw new \RuntimeException("Down method not found in migration $migration");
            }

            $stmt = $this->pdo->prepare("DELETE FROM migrations WHERE migration = :migration");
            $stmt->execute(['migration' => $migration]);

            $this->pdo->commit();
            echo "Rolled back migration: $migration\n";
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            echo "Failed to rollback migration $migration: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}