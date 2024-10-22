<?php // TODO: MAKE THIS A COMMAND

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
    ['Stan Rochat', 'stan@stan.stan', password_hash('stan', PASSWORD_DEFAULT)],
    ['John Doe', 'john@example.com', password_hash('password123', PASSWORD_DEFAULT)],
    ['Jane Smith', 'jane@example.com', password_hash('password456', PASSWORD_DEFAULT)],
    ['Bob Johnson', 'bob@example.com', password_hash('password789', PASSWORD_DEFAULT)],
    ['Alice Williams', 'alice@example.com', password_hash('passwordabc', PASSWORD_DEFAULT)],
    ['Charlie Brown', 'charlie@example.com', password_hash('passworddef', PASSWORD_DEFAULT)],
    ['Diana Ross', 'diana@example.com', password_hash('passwordghi', PASSWORD_DEFAULT)],
    ['Ethan Hunt', 'ethan@example.com', password_hash('passwordjkl', PASSWORD_DEFAULT)],
    ['Fiona Apple', 'fiona@example.com', password_hash('passwordmno', PASSWORD_DEFAULT)],
    ['George Lucas', 'george@example.com', password_hash('passwordpqr', PASSWORD_DEFAULT)],
    ['Hannah Montana', 'hannah@example.com', password_hash('passwordstu', PASSWORD_DEFAULT)],
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
    ['Optimize database', 'Improve database performance', 'Not Started', time() + 432000, 'High'],
    ['Implement new feature', 'Add user authentication system', 'In Progress', time() + 864000, 'High'],
    ['Fix reported bugs', 'Address issues from bug tracker', 'Pending', time() + 345600, 'Medium'],
    ['Prepare presentation', 'Create slides for client meeting', 'Not Started', time() + 172800, 'Medium'],
    ['Conduct user testing', 'Organize and run user testing sessions', 'Not Started', time() + 1036800, 'Low'],
    ['Refactor legacy code', 'Modernize old codebase', 'In Progress', time() + 1728000, 'Medium'],
    ['Deploy to production', 'Prepare and execute production deployment', 'Pending', time() + 86400, 'High'],
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

$stmt = $conn->prepare("INSERT INTO sub_tasks (task_id, title, beschrijving, status) VALUES (?, ?, ?, ?)");

foreach ($sub_tasks as $sub_task) {
    $stmt->bind_param("isss", $sub_task[0], $sub_task[1], $sub_task[2], $sub_task[3]);
    $stmt->execute();
    echo "Inserted sub-task: " . $sub_task[1] . "\n";
}

// Seed User-Task relationships
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

$stmt = $conn->prepare("INSERT INTO task_user (user_id, task_id) VALUES (?, ?)");

foreach ($user_tasks as $user_task) {
    $stmt->bind_param("ii", $user_task[0], $user_task[1]);
    $stmt->execute();
    echo "Inserted user-task relationship: " . $user_task[0] . " -> " . $user_task[1] . "\n";
}

$conn->close();

echo "Seeding completed successfully!";