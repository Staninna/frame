<?php

namespace app\migrations;

use PDO;
use Frame\Cli\Db\Migration;

class CreateTaskUserMigration extends Migration
{
    public function up(PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS task_user (
                user_id INT NOT NULL,
                task_id INT NOT NULL,
                PRIMARY KEY (user_id, task_id),
                FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
                FOREIGN KEY (task_id) REFERENCES tasks (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE task_user");
    }
}
