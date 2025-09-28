<?php

namespace Core;

class Cache
{
    protected static string $cachePath = __DIR__ . '/../storage/cache/app/';

    /**
     * Get an item from the cache, or execute the callback and store the result.
     */
    public static function remember( string $key, int $minutes, callable $callback )
    {
        if ( static::has( $key ) )
        {
            return static::get( $key );
        }

        $value = $callback();
        static::put( $key, $value, $minutes );
        return $value;
    }

    /**
     * @param string $key
     * @param $default
     * @return mixed
     */
    public static function get( string $key, $default = null )
    {
        $path = static::$cachePath . md5( $key );
        if ( !file_exists( $path ) )
        {
            return $default;
        }

        $data = unserialize( file_get_contents( $path ) );
        if ( time() > $data['expires'] )
        {
            unlink( $path ); // Cache expired
            return $default;
        }
        return $data['value'];
    }

    /**
     * @param string $key
     * @param $value
     * @param int $minutes
     */
    public static function put( string $key, $value, int $minutes ): void
    {
        if ( !is_dir( static::$cachePath ) )
        {
            mkdir( static::$cachePath, 0777, true );
        }

        $data = [
            'value'   => $value,
            'expires' => time() + ( $minutes * 60 ),
        ];
        file_put_contents( static::$cachePath . md5( $key ), serialize( $data ) );
    }

    /**
     * @param string $key
     */
    public static function has( string $key ): bool
    {
        return static::get( $key ) !== null;
    }

    /**
     * @param string $key
     */
    public static function forget( string $key ): void
    {
        $path = static::$cachePath . md5( $key );
        if ( file_exists( $path ) )
        {
            unlink( $path );
        }
    }

    public static function flush(): bool
    {
        $files = glob( static::$cachePath . '*' );
        foreach ( $files as $file )
        {
            if ( is_file( $file ) )
            {
                unlink( $file );
            }
        }
        return true;
    }
}
