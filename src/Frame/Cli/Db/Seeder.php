<?php

namespace Frame\Cli\Db;

use PDO;

abstract class Seeder
{
    abstract public function run(PDO $pdo): void;
}