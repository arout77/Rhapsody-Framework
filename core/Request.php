<?php

namespace Core;

class Request
{
    private readonly array $getParams;
    private readonly array $postParams;
    private readonly array $cookies;
    private readonly array $files;
    private readonly array $server;

    public function __construct()
    {
        $this->getParams  = $_GET;
        $this->postParams = $_POST;
        $this->cookies    = $_COOKIE;
        $this->files      = $_FILES;
        $this->server     = $_SERVER;
    }

    public function getMethod(): string
    {
        // Corrected: Access the 'REQUEST_METHOD' key from the server array
        return strtolower( $this->server['REQUEST_METHOD'] ?? 'get' );
    }

    /**
     * Gets the clean request path for the router.
     * This method handles subdirectories and normalizes trailing slashes.
     * @return string
     */
    public function getPath(): string
    {
        // Get the full request URI
        $path = $this->server['REQUEST_URI'] ?? '/';

        // Remove query string parameters
        $position = strpos( $path, '?' );
        if ( $position !== false )
        {
            $path = substr( $path, 0, $position );
        }

        // Handle subdirectory installations
        $scriptPath = dirname( $this->server['SCRIPT_NAME'] );
        if ( $scriptPath !== '/' && str_starts_with( $path, $scriptPath ) )
        {
            $path = substr( $path, strlen( $scriptPath ) );
        }

        // Remove the trailing slash from the path, but only if it's not the root path.
        if ( strlen( $path ) > 1 )
        {
            $path = rtrim( $path, '/' );
        }

        // Ensure the path starts with a '/' and is not empty
        return empty( $path ) ? '/' : $path;
    }

    /**
     * Gets the request body data (from $_POST).
     *
     * @return array
     */
    public function getBody(): array {
        // For simplicity, we'll focus on POST data.
        // A more advanced version would handle JSON bodies as well.
        $body = [];
        if ( $this->getMethod() === 'post' )
        {
            foreach ( $_POST as $key => $value )
            {
                // Basic sanitization
                $body[$key] = filter_input( INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS );
            }
        }
        return $body;
    }

    /**
     * @param string $key
     * @param $default
     * @return mixed
     */
    public function get( string $key, $default = null )
    {
        return $this->getParams[$key] ?? $default;
    }

    /**
     * @param string $key
     * @param $default
     * @return mixed
     */
    public function post( string $key, $default = null )
    {
        return $this->postParams[$key] ?? $default;
    }

    /**
     * Primarily used for pagination
     * @param string $key
     * @param $default
     */
    public function getQueryParam( string $key, $default = null )
    {
        // Use filter_input for basic security
        return filter_input( INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS ) ?? $default;
    }
}
