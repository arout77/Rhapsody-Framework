<?php

namespace Core\Cache;

class FileCacheDriver implements CacheInterface
{
    protected string $cachePath = __DIR__ . '/../../storage/cache/app/';

    /**
     * @param string $key
     * @param $default
     * @return mixed
     */
    public function get( string $key, $default = null )
    {
        $path = $this->cachePath . md5( $key );
        if ( !file_exists( $path ) ) {
            return $default;
        }

        $data = unserialize( file_get_contents( $path ) );
        if ( time() > $data['expires'] ) {
            unlink( $path );
            return $default;
        }
        return $data['value'];
    }

    /**
     * @param string $key
     * @param $value
     * @param int $minutes
     */
    public function put( string $key, $value, int $minutes ): void
    {
        if ( !is_dir( $this->cachePath ) ) {
            mkdir( $this->cachePath, 0777, true );
        }
        $data = [
            'value'   => $value,
            'expires' => time() + ( $minutes * 60 ),
        ];
        file_put_contents( $this->cachePath . md5( $key ), serialize( $data ) );
    }

    /**
     * Checks for key existence and expiry directly, without calling get().
     * Fixes false negatives for legitimately cached falsy values (null, 0, false, '').
     *
     * @param string $key
     * @return bool
     */
    public function has( string $key ): bool
    {
        $path = $this->cachePath . md5( $key );
        if ( !file_exists( $path ) ) {
            return false;
        }
        $data = unserialize( file_get_contents( $path ) );
        if ( time() > $data['expires'] ) {
            unlink( $path );
            return false;
        }
        return true;
    }

    /**
     * @param string $key
     */
    public function forget( string $key ): void
    {
        $path = $this->cachePath . md5( $key );
        if ( file_exists( $path ) ) {
            unlink( $path );
        }
    }

    public function flush(): bool
    {
        $files = glob( $this->cachePath . '*' );
        foreach ( $files as $file ) {
            if ( is_file( $file ) ) {
                unlink( $file );
            }
        }
        return true;
    }
}
