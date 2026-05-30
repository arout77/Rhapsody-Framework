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
        return strtolower($this->server['REQUEST_METHOD'] ?? 'get');
    }

    /**
     * Strips the APP_BASE_URL prefix and query string from the URI
     * to produce a clean path for the router.
     */
    public function getPath(): string
    {
        $path     = $this->server['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');
        if ($position !== false) {
            $path = substr($path, 0, $position);
        }

        $baseUrl = $_ENV['APP_BASE_URL'] ?? '';

        if (! empty($baseUrl) && $baseUrl !== '/' && str_starts_with($path, $baseUrl)) {
            $path = substr($path, strlen($baseUrl));
        }

        if (strlen($path) > 1) {
            $path = rtrim($path, '/');
        }
        return empty($path) ? '/' : $path;
    }

    /**
     * Gets the request body, supporting both traditional form data and JSON payloads.
     * Returns raw values — sanitization is the responsibility of the Validator or controller.
     * Behaviour is now consistent regardless of how the client sends data.
     *
     * @return array
     */
    public function getBody(): array
    {
        // 1. JSON content type (always attempt, regardless of HTTP method)
        if (isset($this->server['CONTENT_TYPE']) && str_contains(strtolower($this->server['CONTENT_TYPE']), 'application/json')) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            return is_array($data) ? $data : [];
        }

        // 2. For POST requests with form data, return $_POST
        if ($this->getMethod() === 'post' && ! empty($this->postParams)) {
            return $this->postParams;
        }

        // 3. Fallback: parse php://input for PUT/PATCH/DELETE with application/x-www-form-urlencoded
        $input = file_get_contents('php://input');
        if (! empty($input) && str_contains($input, '=')) {
            parse_str($input, $data);
            return $data;
        }

        return [];
    }

    /**
     * @param string $key
     * @param $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->getParams[$key] ?? $default;
    }

    /**
     * @param string $key
     * @param $default
     * @return mixed
     */
    public function post(string $key, $default = null)
    {
        return $this->postParams[$key] ?? $default;
    }

    /**
     * @param string $key
     * @param $default
     */
    public function getQueryParam(string $key, $default = null)
    {
        return filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS) ?? $default;
    }

    /**
     * @return mixed
     */
    public function getFiles(): array
    {
        return $this->files;
    }
}
