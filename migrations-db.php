<?php

use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__ . '/vendor/autoload.php';

(new Dotenv())->loadEnv(__DIR__ . '/.env');

return [
    'dbname' => getenv('DB_NAME'),
    'user' => getenv('DB_USER'),
    'password' => getenv('DB_PASSWORD'),
    'host' => getenv('DB_HOST'),
    'driver' => getenv('DB_DRIVER'),
];
