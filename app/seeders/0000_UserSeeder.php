<?php

namespace app\seeders;

use Frame\Cli\Db\Seeder;

class UserSeeder extends Seeder
{
    public function run(\PDO $pdo): void
    {
        $password = 'password';

        $users = [
            ['stan', 'stan@stan.stan', password_hash('stan', PASSWORD_DEFAULT)],
            ['John Doe', 'john@example.com', password_hash($password, PASSWORD_DEFAULT)],
            ['Jane Smith', 'jane@example.com', password_hash($password, PASSWORD_DEFAULT)],
            ['Bob Johnson', 'bob@example.com', password_hash($password, PASSWORD_DEFAULT)],
            ['Alice Williams', 'alice@example.com', password_hash($password, PASSWORD_DEFAULT)],
            ['Charlie Brown', 'charlie@example.com', password_hash($password, PASSWORD_DEFAULT)],
            ['Diana Ross', 'diana@example.com', password_hash($password, PASSWORD_DEFAULT)],
            ['Ethan Hunt', 'ethan@example.com', password_hash($password, PASSWORD_DEFAULT)],
            ['Fiona Apple', 'fiona@example.com', password_hash($password, PASSWORD_DEFAULT)],
            ['George Lucas', 'george@example.com', password_hash($password, PASSWORD_DEFAULT)],
            ['Hannah Montana', 'hannah@example.com', password_hash($password, PASSWORD_DEFAULT)]
        ];

        foreach ($users as $user) {
            $stmt = $pdo->prepare("INSERT INTO users (naam, email, wachtwoord) VALUES (?, ?, ?)");
            $stmt->execute($user);
        }
    }
}