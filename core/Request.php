<?php

namespace Core;

class Request
{
    private readonly array $getParams;
    private readonly array $postParams;
    private readonly array $cookies;
    private readonly array $files;
    private readonly array $server;
    public readonly string $uri;

    public function __construct()
    {
        $this->getParams  = $_GET;
        $this->postParams = $_POST;
        $this->cookies    = $_COOKIE;
        $this->files      = $_FILES;
        $this->server     = $_SERVER;
        $this->uri        = $_SERVER['REQUEST_URI'] ?? '/';
    }

    public function getMethod(): string
    {
        return strtolower( $this->server['REQUEST_METHOD'] ?? 'get' );
    }

    public function getPath(): string
    {
        $path     = $this->server['REQUEST_URI'] ?? '/';
        $position = strpos( $path, '?' );
        if ( $position !== false )
        {
            $path = substr( $path, 0, $position );
        }
        $scriptPath = dirname( $this->server['SCRIPT_NAME'] );
        if ( $scriptPath !== '/' && str_starts_with( $path, $scriptPath ) )
        {
            $path = substr( $path, strlen( $scriptPath ) );
        }
        if ( strlen( $path ) > 1 )
        {
            $path = rtrim( $path, '/' );
        }
        return empty( $path ) ? '/' : $path;
    }

    /**
     * --- As of v1.0.1 ---
     * Fixes issue with JSON payloads not being included in $_POST
     * Gets the request body, supporting both traditional form data
     * and JSON payloads.
     *
     * @return array
     */
    public function getBody(): array {
        if ( $this->getMethod() !== 'post' )
        {
            return [];
        }

        // Check content type for JSON
        if ( isset( $this->server['CONTENT_TYPE'] ) && str_contains( strtolower( $this->server['CONTENT_TYPE'] ), 'application/json' ) )
        {
            $json = file_get_contents( 'php://input' );
            return json_decode( $json, true ) ?? [];
        }

        // Fallback to standard POST data for forms
        $body = [];
        foreach ( $_POST as $key => $value )
        {
            $body[$key] = filter_input( INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS );
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
     * @param string $key
     * @param $default
     */
    public function getQueryParam( string $key, $default = null )
    {
        return filter_input( INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS ) ?? $default;
    }

    /**
     * @return mixed
     */
    public function getFiles(): array {
        return $this->files;
    }
}
