<?php

if (extension_loaded('mysqli')) {
    echo "MySQLi extension is installed and enabled.\n";
} else {
    echo "MySQLi extension is not installed or not enabled.\n";
}

$host = getenv('DB_HOST') ?: 'mysql';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: 'root';
$dbname = getenv('DB_NAME') ?: 'test';

echo '<pre>';
echo "Attempting to connect with the following details:\n";
echo "Host: $host\n";
echo "Username: $username\n";
echo "Database: $dbname\n";

// Try to resolve the hostname
$ip = gethostbyname($host);
echo "Resolved IP for $host: $ip\n";

// Create connection
try {
    $conn = new mysqli($host, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error . "\n");
    }

    echo "Connected successfully to the database.\n";
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
    exit;
}

// Seed Users
$users = [
    ['John Doe', 'john@example.com', password_hash('password123', PASSWORD_DEFAULT)],
    ['Jane Smith', 'jane@example.com', password_hash('password456', PASSWORD_DEFAULT)],
    ['Bob Johnson', 'bob@example.com', password_hash('password789', PASSWORD_DEFAULT)],
];

$stmt = $conn->prepare("INSERT INTO users (naam, email, wachtwoord) VALUES (?, ?, ?)");

foreach ($users as $user) {
    $stmt->bind_param("sss", $user[0], $user[1], $user[2]);
    $stmt->execute();
    echo "Inserted user: " . $user[0] . "\n";
}

// Seed Tasks
$tasks = [
    ['Complete project', 'Finish the main project tasks', 'In Progress', time() + 604800, 'High'],
    ['Review code', 'Perform code review for recent changes', 'Pending', time() + 259200, 'Medium'],
    ['Update documentation', 'Update user manual', 'Not Started', time() + 1209600, 'Low'],
];

$stmt = $conn->prepare("INSERT INTO tasks (title, beschrijving, status, verval_datum, prioriteit) VALUES (?, ?, ?, ?, ?)");

foreach ($tasks as $task) {
    $stmt->bind_param("sssis", $task[0], $task[1], $task[2], $task[3], $task[4]);
    $stmt->execute();
    echo "Inserted task: " . $task[0] . "\n";
}

// Seed Sub-tasks
$sub_tasks = [
    [1, 'Design UI', 'Create user interface design', 'In Progress'],
    [1, 'Implement backend', 'Develop server-side logic', 'Not Started'],
    [2, 'Check for bugs', 'Identify and list any bugs', 'Pending'],
];

$stmt = $conn->prepare("INSERT INTO sub_tasks (task_id, title, beschrijving, status) VALUES (?, ?, ?, ?)");

foreach ($sub_tasks as $sub_task) {
    $stmt->bind_param("isss", $sub_task[0], $sub_task[1], $sub_task[2], $sub_task[3]);
    $stmt->execute();
    echo "Inserted sub-task: " . $sub_task[1] . "\n";
}

// Seed User-Task relationships
$user_tasks = [
    [1, 1],
    [1, 2],
    [2, 2],
    [2, 3],
    [3, 1],
];

$stmt = $conn->prepare("INSERT INTO user_task (user_id, task_id) VALUES (?, ?)");

foreach ($user_tasks as $user_task) {
    $stmt->bind_param("ii", $user_task[0], $user_task[1]);
    $stmt->execute();
    echo "Inserted user-task relationship: " . $user_task[0] . " -> " . $user_task[1] . "\n";
}

$conn->close();

echo "Seeding completed successfully!";
