<?php

namespace Frame\Cli\Command\BuiltIns;

use Frame\Cli\Command\Command;
use Frame\Cli\Db\Migration;
use PDO;
use RuntimeException;

// Seems unused but gets dynamically loaded by the command loader
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
        require_once __DIR__ . '/../../Db/Migration.php';
        $this->createMigrationsTableIfNotExists();
        $appliedMigrations = $this->getAppliedMigrations();
        $availableMigrations = $this->getAvailableMigrations();

        $migrationsToApply = array_diff($availableMigrations, $appliedMigrations);

        if (empty($migrationsToApply)) {
            echo "--------------------------------------------------------------------------------\n";
            echo "No new migrations to apply.\n";
            echo "--------------------------------------------------------------------------------\n";
            return;
        }

        echo "--------------------------------------------------------------------------------\n";
        echo "Applying " . count($migrationsToApply) . " migration(s)...\n";
        echo "--------------------------------------------------------------------------------\n";

        $failed = 0;
        foreach ($migrationsToApply as $migration) {
            echo "Applying migration: $migration\n";
            $this->applyMigration($migration, $failed);
        }

        echo "--------------------------------------------------------------------------------\n";
        echo "Applied " . count($migrationsToApply) . " migration(s) successfully.\n";
        if ($failed > 0) {
            echo "Failed to apply $failed migration(s).\n";
        }
        echo "--------------------------------------------------------------------------------\n";
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
            return preg_match('/^\d+_.*$/', $file);
        });
    }

    private function applyMigration(string $migration, int &$failed): void
    {
        require_once $this->migrationsPath . '/' . $migration;

        $className = $this->getMigrationClassName($migration);
        $fullClassName = "migrations\\$className"; // TODO: Make this configurable in config in sync with migrations path

        if (!class_exists($fullClassName)) {
            throw new RuntimeException("Migration class '$fullClassName' not found in file '$migration'");
        }

        $instance = new $fullClassName();

        if (!$instance instanceof Migration) {
            throw new RuntimeException("Migration class '$fullClassName' must extend Frame\\Cli\\Db\\Migration");
        }

        $this->pdo->beginTransaction();

        try {
            if (!method_exists($instance, 'up')) {
                throw new RuntimeException("Up method not found in migration $migration");
            }

            $instance->up($this->pdo);
            $stmt = $this->pdo->prepare("INSERT INTO migrations (migration) VALUES (:migration)");
            $stmt->execute(['migration' => $migration]);

            if ($this->pdo->inTransaction()) {
                $this->pdo->commit();
            }
            echo "Applied migration: $migration\n";
        } catch (\Exception $e) {
            echo "Failed to apply migration $migration: " . $e->getMessage() . "\n";
            $failed++;
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            exit(1);
        }
    }

    /**
     * Converts a migration filename to a proper class name.
     *
     * Examples:
     * - "0000_create_users.php" -> "CreateUsersMigration"
     * - "0001_create_tasks.php" -> "CreateTasksMigration"
     * - "0002_create_task_user.php" -> "CreateTaskUserMigration"
     * - "000000000213243_update_users.php" -> "UpdateUsersMigration"
     */
    private function getMigrationClassName(string $migration): string
    {
        // Remove the numeric prefix and .php extension
        $withoutNumber = substr(strstr($migration, '_'), 1);
        $withoutExtension = str_replace('.php', '', $withoutNumber);

        // Convert to proper case and remove underscores
        $words = str_replace('_', ' ', $withoutExtension);
        $camelCase = str_replace(' ', '', ucwords($words));

        return $camelCase . 'Migration';
    }
}