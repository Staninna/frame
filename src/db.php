<?php

if (false) {
    echo '<pre>';
    echo "Checking PDO drivers:\n";
    foreach (PDO::getAvailableDrivers() as $driver) {
        echo "- $driver\n";
    }

    if (in_array('mysql', PDO::getAvailableDrivers())) {
        echo "PDO MySQL driver is installed.\n";
    } else {
        echo "PDO MySQL driver is NOT installed.\n";
    }
    echo '</pre>';
}

$host = getenv('DB_HOST') ?: 'mysql';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: 'root';
$dbname = getenv('DB_NAME') ?: 'test';

$pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Initialize models with connection
\models\User::setDatabaseConnection($pdo);
\models\Task::setDatabaseConnection($pdo);
\models\SubTask::setDatabaseConnection($pdo);