<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }

        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }

        .task {
            background-color: #fff;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .task h2 {
            color: #3498db;
            margin-top: 0;
        }

        .task-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .task-info p {
            margin: 5px 0;
        }

        .subtasks {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .subtask {
            background-color: #ecf0f1;
            border-radius: 3px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .subtask h3 {
            color: #2980b9;
            margin-top: 0;
            font-size: 1.1em;
        }

        .status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.9em;
            font-weight: bold;
        }

        .users-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .user {
            background-color: #fff;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .status-pending {
            background-color: #f39c12;
            color: #fff;
        }

        .status-in-progress {
            background-color: #3498db;
            color: #fff;
        }

        .status-completed {
            background-color: #2ecc71;
            color: #fff;
        }
    </style>
</head>
<body>
<h1>Tasks</h1>

<?php

use app\models\{User};
use app\models\Task;

/** @var array<Task> $tasks */
/** @var array<User> $users */
?>

<?php foreach ($tasks as $task): ?>
    <div class="task">
        <h2><?= htmlspecialchars($task->title) ?></h2>
        <p><?= htmlspecialchars($task->beschrijving) ?></p>
        <div class="task-info">
            <p>
                    <span class="status status-<?= strtolower(str_replace(' ', '-', $task->status)) ?>">
                        <?= htmlspecialchars($task->status) ?>
                    </span>
            </p>
            <p>Due: <?= htmlspecialchars(date('Y-m-d', $task->verval_datum)) ?></p>
            <p>Priority: <?= htmlspecialchars($task->prioriteit) ?></p>
        </div>

        <div class="users">
            <h3>Users</h3>
            <div class="users-list">
                <?php foreach ($task->users() as $user): ?>
                    <div class="user">
                        <h4><?= htmlspecialchars($user->naam) ?></h4>
                        <p><?= htmlspecialchars($user->email) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (!empty($task->subTasks())): ?>
            <div class="subtasks">
                <h3>Subtasks</h3>
                <?php foreach ($task->subTasks() as $subTask): ?>
                    <div class="subtask">
                        <h3><?= htmlspecialchars($subTask->title) ?></h3>
                        <p><?= htmlspecialchars($subTask->beschrijving) ?></p>
                        <p>
                                <span class="status status-<?= strtolower(str_replace(' ', '-', $subTask->status)) ?>">
                                    <?= htmlspecialchars($subTask->status) ?>
                                </span>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

</body>
</html>