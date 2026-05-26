<?php
namespace Core;

use Core\Cache;
use Core\Database;
use PDO;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

abstract class BaseController
{
    protected Environment $twig;
    protected PDO $db;
    protected Cache $cache;
    protected ?array $assetManifest = null;

    /**
     * @param Environment $twig
     */
    public function __construct(Environment $twig)
    {
        $this->twig  = $twig;
        $this->db    = Database::getInstance();
        $this->cache = Cache::getInstance();

        $this->registerReactAssetHelpers();
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
        // echo self::react_asset('src/main.jsx');
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

    /**
     * Registers the React asset functions with the Twig environment
     */
    private function registerReactAssetHelpers(): void
    {
        $this->twig->addFunction(new TwigFunction('react_asset', [$this, 'react_asset']));
        $this->twig->addFunction(new TwigFunction('react_asset_css', [$this, 'react_asset_css']));
    }

    /**
     * Resolves JavaScript files dynamically via manifest or local fallback
     */
    public function react_asset(string $entry): string
    {
        $baseUrl  = $this->getBaseUrl();
        $manifest = $this->loadManifest();

        // 1. Direct key matching (e.g., 'src/main.jsx')
        if (isset($manifest[$entry]['file'])) {
            return $baseUrl . '/public/build/' . $manifest[$entry]['file'];
        }

        // 2. Loose matching fallback (searches keys matching the end of the string)
        foreach ($manifest as $key => $value) {
            if (str_ends_with($key, $entry) && isset($value['file'])) {
                return $baseUrl . '/public/build/' . $value['file'];
            }
        }

        // 3. Fallback to flat mix structures if they exist
        if (isset($manifest['/public/js/' . $entry])) {
            return $baseUrl . $manifest['/public/js/' . $entry];
        }

        // 4. Emergency local development path if manifest doesn't contain the key
        return $baseUrl . '/public/js/' . $entry;
    }

    /**
     * Resolves CSS files dynamically via manifest or local fallback
     */
    public function react_asset_css(string $entry): string
    {
        $baseUrl  = $this->getBaseUrl();
        $manifest = $this->loadManifest();

        // 1. Production lookup for CSS attached to a JS entry point (Vite style)
        if (isset($manifest[$entry]['css'][0])) {
            // Points cleanly to /public/build/assets/style-xxxx.css
            return $baseUrl . '/public/build/' . $manifest[$entry]['css'][0];
        }

        // 2. Production lookup for dedicated flat CSS keys
        if (isset($manifest[$entry]['file'])) {
            return $baseUrl . '/public/build/' . $manifest[$entry]['file'];
        }

        if (isset($manifest['/public/css/' . $entry])) {
            return $baseUrl . $manifest['/public/css/' . $entry];
        }

        // 3. Local Development Fallback
        return $baseUrl . '/public/css/' . $entry;
    }

    /**
     * Internal cache engine to minimize file reads
     */
    protected function loadManifest(): array
    {
        if ($this->assetManifest !== null) {
            return $this->assetManifest;
        }

        // Points directly to the file you found!
        $viteManifest = dirname(__DIR__) . '/frontend/dist/.vite/manifest.json';
        $mixManifest  = dirname(__DIR__) . '/mix-manifest.json';

        if (file_exists($viteManifest)) {
            $this->assetManifest = json_decode(file_get_contents($viteManifest), true) ?? [];
        } elseif (file_exists($mixManifest)) {
            $this->assetManifest = json_decode(file_get_contents($mixManifest), true) ?? [];
        } else {
            $this->assetManifest = [];
        }

        return $this->assetManifest;
    }

    /**
     * Helper to get base application URL safely
     */
    private function getBaseUrl(): string
    {
        // Adjust this to pull from your global config layer or environment variables
        return $_ENV['APP_URL'] . $_ENV['APP_BASE_URL'] ?? '';
    }

}
