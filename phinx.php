<?php

// This bootstrap is necessary to load the .env file
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad(); // Using safeLoad prevents crashes if .env is missing

// Check if database credentials are present
$dbConfigured = ! empty($_ENV['DB_HOST']) && ! empty($_ENV['DB_NAME']);

$config = [
    'paths'         => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds'      => '%%PHINX_CONFIG_DIR%%/db/seeds',
    ],
    'environments'  => [
        'default_migration_table' => 'phinxlog',
        'default_environment'     => 'development',
    ],
    'version_order' => 'creation',
];

// Only add the development environment if DB is configured
if ($dbConfigured) {
    $config['environments']['development'] = [
        'adapter' => 'mysql',
        'host'    => $_ENV['DB_HOST'],
        'name'    => $_ENV['DB_NAME'],
        'user'    => $_ENV['DB_USER'] ?? 'root',
        'pass'    => $_ENV['DB_PASS'] ?? 'root',
        'port'    => $_ENV['DB_PORT'] ?? '3306',
        'charset' => 'utf8mb4',
    ];
}

return $config;
