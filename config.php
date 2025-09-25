<?php

/**
 * Rhapsody Framework Configuration
 *
 * This file reads settings directly from the $_ENV superglobal,
 * which is reliably populated by the Dotenv library.
 */

return [
    /**
     * The base URL of your application.
     */
    'base_url' => $_ENV['APP_BASE_URL'] ?? '/rhapsody',

    /**
     * Database connection settings.
     * Using $_ENV is more reliable than getenv()
     */
    'database' => [
        'host'     => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port'     => $_ENV['DB_PORT'] ?? 3306,
        'dbname'   => $_ENV['DB_NAME'] ?? 'rhapsody_db',
        'user'     => $_ENV['DB_USER'] ?? 'root',
        'password' => $_ENV['DB_PASS'] ?? '',
        'charset'  => 'utf8mb4',
    ],
];
