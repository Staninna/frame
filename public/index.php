<?php

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/src/Frame/require.php';
require_once BASE_PATH . '/app/require.php';

use Frame\Boot\Bootstrap;

$app = new Bootstrap();

// TODO: Make seeder command/interface
if (false) { require_once BASE_PATH . '/app/seed.php'; }

$app->run();

// TODO: Make functions.php in the framework for common functions to get shit out of the config and other common shit