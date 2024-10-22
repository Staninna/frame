<?php

namespace Frame\Cli\Command\BuiltIns;

use Frame\Cli\Command\Command;
use Frame\Cli\Db\Migration;
use PDO;
use RuntimeException;

// Seems unused but gets dynamically loaded by the command loader
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
        require_once __DIR__ . '/../../Db/Migration.php';
        $steps = isset($arguments[0]) ? (int)$arguments[0] : 1;

        $appliedMigrations = $this->getAppliedMigrations();

        if (empty($appliedMigrations)) {
            echo "No migrations to rollback.\n";
            return;
        }

        $migrationsToRollback = array_slice($appliedMigrations, -$steps);

        echo "--------------------------------------------------------------------------------\n";
        echo "Rolling back " . count($migrationsToRollback) . " migration(s)...\n";
        echo "--------------------------------------------------------------------------------\n";

        $failed = 0;
        foreach (array_reverse($migrationsToRollback) as $migration) {
            $this->rollbackMigration($migration, $failed);
        }

        echo "--------------------------------------------------------------------------------\n";
        echo "Rolled back " . count($migrationsToRollback) . " migration(s) successfully.\n";
        if ($failed > 0) {
            echo "Failed to roll back $failed migration(s).\n";
        }
        echo "--------------------------------------------------------------------------------\n";
    }

    private function getAppliedMigrations(): array
    {
        $stmt = $this->pdo->query("SELECT migration FROM migrations ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function rollbackMigration(string $migration, int &$failed): void
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
            if (!method_exists($instance, 'down')) {
                throw new RuntimeException("Down method not found in migration $migration");
            }

            $instance->down($this->pdo);
            $stmt = $this->pdo->prepare("DELETE FROM migrations WHERE migration = :migration");
            $stmt->execute(['migration' => $migration]);

            if ($this->pdo->inTransaction()) {
                $this->pdo->commit();
            }
            echo "Rolled back migration: $migration\n";
        } catch (\Exception $e) {
            echo "Failed to rollback migration $migration: " . $e->getMessage() . "\n";
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