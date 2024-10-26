<?php

namespace app\seeders;

use Frame\Cli\Db\Seeder;

class SubTaskSeeder extends Seeder
{
    public function run(\PDO $pdo): void
    {
        $sub_tasks = [
            [1, 'Design UI', 'Create user interface design', 'In Progress'],
            [1, 'Implement backend', 'Develop server-side logic', 'Not Started'],
            [2, 'Check for bugs', 'Identify and list any bugs', 'Pending'],
            [3, 'Write user guide', 'Create comprehensive user guide', 'Not Started'],
            [3, 'Update API docs', 'Revise API documentation', 'Not Started'],
            [4, 'Analyze query performance', 'Identify slow queries', 'Not Started'],
            [4, 'Implement indexing', 'Add necessary database indexes', 'Not Started'],
            [5, 'Design authentication flow', 'Create user authentication process', 'In Progress'],
            [5, 'Implement login/logout', 'Develop login and logout functionality', 'Not Started'],
            [6, 'Reproduce reported issues', 'Test and confirm reported bugs', 'Pending'],
            [6, 'Fix critical bugs', 'Address high-priority issues', 'Not Started'],
            [7, 'Gather project stats', 'Collect relevant project statistics', 'Not Started'],
            [7, 'Create slide deck', 'Design presentation slides', 'Not Started'],
            [8, 'Recruit test users', 'Find participants for user testing', 'Not Started'],
            [8, 'Prepare test scenarios', 'Develop user testing scripts', 'Not Started'],
            [9, 'Identify refactoring targets', 'Locate code areas needing modernization', 'In Progress'],
            [9, 'Update dependencies', 'Upgrade project dependencies', 'Not Started'],
            [10, 'Run final tests', 'Perform pre-deployment testing', 'Pending'],
            [10, 'Prepare rollback plan', 'Develop strategy for potential rollback', 'Not Started'],
        ];

        foreach ($sub_tasks as $sub_task) {
            $stmt = $pdo->prepare("INSERT INTO sub_tasks (task_id, title, beschrijving, status) VALUES (?, ?, ?, ?)");
            $stmt->execute($sub_task);
        }
    }
}