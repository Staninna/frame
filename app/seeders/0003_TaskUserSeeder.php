<?php

namespace app\seeders;

use Frame\Cli\Db\Seeder;

class TaskUserSeeder extends Seeder
{
    public function run(\PDO $pdo): void
    {
        $user_tasks = [
            [1, 1], [1, 2], [1, 5],
            [2, 2], [2, 3], [2, 7],
            [3, 1], [3, 4], [3, 6],
            [4, 3], [4, 5], [4, 8],
            [5, 4], [5, 6], [5, 9],
            [6, 7], [6, 8], [6, 10],
            [7, 1], [7, 9], [7, 10],
            [8, 2], [8, 5], [8, 8],
            [9, 3], [9, 6], [9, 9],
            [10, 4], [10, 7], [10, 10],
        ];

        foreach ($user_tasks as $user_task) {
            $stmt = $pdo->prepare("INSERT INTO task_user (user_id, task_id) VALUES (?, ?)");
            $stmt->execute($user_task);
        }
    }
}