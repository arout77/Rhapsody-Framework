<?php
namespace Core;

use Core\Cache;
use Core\Database;
use PDO;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

abstract class BaseController
{
    protected Environment $twig;
    protected PDO $db;
    protected Cache $cache;

    /**
     * @param Environment $twig
     */
    public function __construct(Environment $twig)
    {
        $this->twig  = $twig;
        $this->db    = Database::getInstance();
        $this->cache = Cache::getInstance();
    }

    /**
     * Renders a view file using Twig.
     *
     * @param string $view The view file to render.
     * @param array  $args Associative array of data to pass to the view.
     * @param array  $meta SEO metadata for the page (e.g., ['title' => 'My Title']).
     * @return Response
     */
    protected function view(string $view, array $args = [], array $meta = []): Response
    {
        // Add default values for meta tags
        $defaults = [
            'title'       => 'Rhapsody - Compose your masterpiece',
            'description' => 'Rhapsody is a modern PHP framework for developers who find full-stack frameworks like Laravel too heavy for their needs, but find micro-frameworks like Slim too bare-bones. It gives you the modern tooling you love—like a powerful CLI, dependency injection, and an ORM—in a simple, performant, and elegant package. It\'s the perfect choice for building fast, maintainable web applications and APIs without the overhead.',
        ];
        $args['meta'] = array_merge($defaults, $meta);

        $output = $this->twig->render($view, $args);

        $response = new Response();
        $response->setContent($output);
        // echo '<script type="module" src="'. self::vite_asset('src/main.jsx') .'"></script>';
        return $response;
    }

    /**
     * Creates and returns a JSON response.
     *
     * @param array $data The data to be encoded as JSON.
     * @param int $statusCode The HTTP status code for the response (defaults to 200 OK).
     * @return Response
     */
    protected function json(array $data, int $statusCode = 200): Response
    {
        $response = new Response();
        $response->setStatusCode($statusCode);
        $response->setHeader('Content-Type', 'application/json');
        $response->setContent(json_encode($data, JSON_PRETTY_PRINT)); // JSON_PRETTY_PRINT makes it readable
        return $response;
    }

}
