<?php

/**
 * Basic Framework Configuration
 */

return [
    // For root directory, use empty quotes ''
    // otherwise enter name of subdirectory,
    // preceeded by forward slash
    'base_url' => '/framework',

    // --- DATABASE CONFIGURATION ---
    'database' => [
        'host'     => 'localhost', // Or '127.0.0.1'
        'port' => 3306,
        'dbname'   => '', // The name of your database
        'user' => '',
        'password' => '',
        'charset'  => 'utf8mb4',
    ],
];
