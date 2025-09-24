<?php

namespace Core;

use PDO;
use PDOException;

/**
 * A Singleton class for managing the database connection.
 */
class Database
{
    /** @var PDO|null The single instance of the PDO connection. */
    private static ?PDO $instance = null;

    /**
     * The constructor is private to prevent direct creation of the object.
     */
    private function __construct()
    {
    }

    /**
     * The clone method is private to prevent cloning of the instance.
     */
    private function __clone()
    {
    }

    /**
     * Gets the single instance of the PDO database connection.
     *
     * @return PDO
     */
    public static function getInstance(): PDO
    {
        if ( self::$instance === null )
        {
            // Load database configuration
            $config   = require __DIR__ . '/../config.php';
            $dbConfig = $config['database'];

            // Data Source Name (DSN) string
            $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetch as associative arrays
                PDO::ATTR_EMULATE_PREPARES => false, // Use real prepared statements
            ];

            try {
                self::$instance = new PDO( $dsn, $dbConfig['user'], $dbConfig['password'], $options );
            }
            catch ( PDOException $e )
            {
                // In a real app, log this error, not just die
                throw new PDOException( $e->getMessage(), (int) $e->getCode() );
            }
        }

        return self::$instance;
    }
}
