<?php

namespace app\seeders;

use Frame\Cli\Db\Seeder;

class TaskSeeder extends Seeder
{
    public function run(\PDO $pdo): void
    {
        $tasks = [
            ['Complete project', 'Finish the main project tasks', 'In Progress', time() + 604800, 'High'],
            ['Review code', 'Perform code review for recent changes', 'Pending', time() + 259200, 'Medium'],
            ['Update documentation', 'Update user manual', 'Not Started', time() + 1209600, 'Low'],
            ['Optimize database', 'Improve database performance', 'Not Started', time() + 432000, 'High'],
            ['Implement new feature', 'Add user authentication system', 'In Progress', time() + 864000, 'High'],
            ['Fix reported bugs', 'Address issues from bug tracker', 'Pending', time() + 345600, 'Medium'],
            ['Prepare presentation', 'Create slides for client meeting', 'Not Started', time() + 172800, 'Medium'],
            ['Conduct user testing', 'Organize and run user testing sessions', 'Not Started', time() + 1036800, 'Low'],
            ['Refactor legacy code', 'Modernize old codebase', 'In Progress', time() + 1728000, 'Medium'],
            ['Deploy to production', 'Prepare and execute production deployment', 'Pending', time() + 86400, 'High'],
        ];

        foreach ($tasks as $task) {
            $stmt = $pdo->prepare("INSERT INTO tasks (title, beschrijving, status, verval_datum, prioriteit) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute($task);
        }
    }
}