<?php

namespace Core;

/**
 * A simple data collector for the developer toolbar.
 * Uses a singleton pattern to be accessible anywhere during the request.
 */
class Debug
{
    private static ?self $instance = null;
    private array $data            = [];
    private float $startTime;
    private int $startMemory;

    private function __construct()
    {}
    public static function getInstance(): self
    {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Starts the timer and records initial memory usage.
     * Should be called at the very beginning of the request.
     */
    public function start(): void
    {
        $this->startTime           = microtime( true );
        $this->startMemory         = memory_get_usage();
        $this->data['php_version'] = phpversion();
    }

    /**
     * Gathers final data points at the end of the request.
     */
    public function end( Response $response, array $config, Route $route = null ): void
    {
        $this->data['execution_time'] = round( ( microtime( true ) - $this->startTime ) * 1000, 2 );
        $this->data['memory_usage']   = round( ( memory_get_peak_usage() - $this->startMemory ) / 1024 / 1024, 2 );
        $this->data['response_code']  = $response->getStatusCode();
        $this->data['app_version']    = $config['app_version'] ?? 'N/A';
        $this->data['session']        = $_SESSION ?? [];

        if ( $route ) {
            $callback = $route->getCallback();
            if ( is_array( $callback ) && count( $callback ) === 2 ) {
                $controller          = explode( '\\', $callback[0] );
                $this->data['route'] = [
                    'method'     => $route->getMethod(),
                    'path'       => $route->getPath(),
                    'controller' => end( $controller ),
                    'action'     => $callback[1],
                ];
            }
        }
    }

    /**
     * @return mixed
     */
    public function getData(): array
    {
        return $this->data;
    }
}
