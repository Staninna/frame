<?php

namespace Frame\Cli\Db;

use PDO;

abstract class Migration
{
    public function up(PDO $pdo): void
    {
    }

    public function down(PDO $pdo): void
    {
    }
}