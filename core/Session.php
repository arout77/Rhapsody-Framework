<?php

namespace Core;

class Session
{
    public static function start(): void
    {
        if ( session_status() === PHP_SESSION_NONE )
        {
            session_start();
        }
    }

    /**
     * @param string $key
     * @param $value
     */
    public static function set( string $key, $value ): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * @param string $key
     * @param $default
     * @return mixed
     */
    public static function get( string $key, $default = null )
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * @param string $key
     */
    public static function has( string $key ): bool
    {
        return isset( $_SESSION[$key] );
    }

    /**
     * @param string $key
     */
    public static function remove( string $key ): void
    {
        unset( $_SESSION[$key] );
    }

    public static function destroy(): void
    {
        session_destroy();
    }

    /**
     * Regenerates the session ID to prevent session fixation attacks.
     */
    public static function regenerate(): void
    {
        session_regenerate_id( true );
    }
}
