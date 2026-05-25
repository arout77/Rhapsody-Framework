<?php

namespace Core;

use Core\Cache\CacheInterface;

class Cache
{
    private static ?self $instance = null;

    /**
     * @param CacheInterface $driver
     */
    public function __construct( protected CacheInterface $driver )
    {}

    /**
     * Stores the resolved Cache instance for static access.
     * Called once during bootstrapping.
     */
    public static function setInstance( self $cache ): void
    {
        self::$instance = $cache;
    }

    /**
     * Returns the shared Cache instance.
     * Mirrors the Database::getInstance() pattern.
     */
    public static function getInstance(): self
    {
        if ( self::$instance === null ) {
            throw new \RuntimeException( 'Cache has not been initialised. Call Cache::setInstance() during bootstrapping.' );
        }
        return self::$instance;
    }
    /**
     * @param string $key
     * @param $default
     * @return mixed
     */
    public function get( string $key, $default = null )
    {
        return $this->driver->get( $key, $default );
    }

    /**
     * @param string $key
     * @param $value
     * @param int $minutes
     */
    public function put( string $key, $value, int $minutes ): void
    {
        $this->driver->put( $key, $value, $minutes );
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function has( string $key ): bool
    {
        return $this->driver->has( $key );
    }

    /**
     * @param string $key
     */
    public function forget( string $key ): void
    {
        $this->driver->forget( $key );
    }

    /**
     * @return mixed
     */
    public function flush(): bool
    {
        return $this->driver->flush();
    }

    /**
     * @param string $key
     * @param int $minutes
     * @param $callback
     * @return mixed
     */
    public function remember( string $key, int $minutes, callable $callback )
    {
        if ( $this->has( $key ) ) {
            return $this->get( $key );
        }

        $value = $callback();
        $this->put( $key, $value, $minutes );
        return $value;
    }
}
