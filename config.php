<?php

/**
 * Rhapsody Framework Configuration
 *
 * This file reads settings from your .env file.
 * It provides default fallback values for safety.
 */

return [
    /**
     * The base URL of your application.
     */
    'base_url' => getenv( 'APP_BASE_URL' ) ?: '/rhapsody',

    /**
     * Database connection settings.
     */
    'database' => [
        'host'     => getenv( 'DB_HOST' ) ?: '127.0.0.1',
        'port'     => getenv( 'DB_PORT' ) ?: 3306,
        'dbname'   => getenv( 'DB_NAME' ) ?: 'rhapsody_db',
        'user'     => getenv( 'DB_USER' ) ?: 'root',
        'password' => getenv( 'DB_PASS' ) ?: '',
        'charset'  => 'utf8mb4',
    ],
];
