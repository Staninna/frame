<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home</title>
</head>
<body>
<h1>Tasks</h1>

<p>Tasks</p>

<?php foreach ($tasks as $task): ?>
    <h2><?= $task->title ?></h2>
    <p><?= $task->beschrijving ?></p>
    <p>Status: <?= $task->status ?></p>
    <p>Verval datum: <?= $task->verval_datum ?></p>
    <p>Prioriteit: <?= $task->prioriteit ?></p>
<?php endforeach; ?>

</body>
</html>