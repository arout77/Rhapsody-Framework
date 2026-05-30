<?php
namespace Core;

/**
 * Global exception and error handler for Rhapsody.
 * Logs all errors and displays appropriate error pages.
 */
class ErrorHandler
{
    private static bool $registered = false;
    private static array $config    = [];

    /**
     * Register all error handling functions.
     */
    public static function register(array $config): void
    {
        if (self::$registered) {
            return;
        }

        self::$config = $config;

        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);

        self::$registered = true;
    }

    /**
     * Convert PHP errors into ErrorException and forward to exception handler.
     */
    public static function handleError(int $level, string $message, string $file, int $line): bool
    {
        if (error_reporting() & $level) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
        return true;
    }

    /**
     * Handle uncaught exceptions.
     */
    public static function handleException(\Throwable $e): void
    {
        self::logError($e);

        $isHttp404 = ($e instanceof \Core\Exceptions\HttpException  && $e->getStatusCode() === 404);

        if (self::isDevelopment() && ! $isHttp404) {
            // Show Whoops for non-404 errors in development
            self::renderWhoops($e);
        } else {
            // For 404 or production errors, show friendly error page
            self::renderProductionError($e);
        }

        exit(1);
    }

    /**
     * Handle fatal shutdown errors (cannot be caught by set_exception_handler).
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $exception = new \ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            );
            self::handleException($exception);
        }
    }

    /**
     * Log the error using the framework's Logger.
     */
    private static function logError(\Throwable $e): void
    {
        $logPath = self::$config['logging']['error_log_path'] ?? __DIR__ . '/../storage/logs/errors.log';
        $logger  = new Logger($logPath);
        $logger->log(
            sprintf(
                "[%s] %s in %s:%d\nStack trace:\n%s",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $e->getTraceAsString()
            ),
            'ERROR'
        );
    }

    /**
     * Render detailed error page using Whoops (development mode).
     */
    private static function renderWhoops(\Throwable $e): void
    {
        $whoops = new \Whoops\Run();
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
        $whoops->handleException($e);
    }

    /**
     * Render a user‑friendly error page in production mode.
     * Respects the active theme and falls back to default theme.
     *
     * @param \Throwable $e
     * @return void
     */
    private static function renderProductionError(\Throwable $e): void
    {
        // Determine HTTP status code from exception, default to 500
        $statusCode = ($e instanceof \Core\Exceptions\HttpException) ? $e->getStatusCode() : 500;
        http_response_code($statusCode);

        // Get the active theme from config (set in .env)
        $theme         = self::$config['theme'] ?? 'default';
        $baseViewsPath = dirname(__DIR__) . '/views/themes/';

        // Check for error template in active theme, then default theme
        $templatePath = null;
        $activePath   = $baseViewsPath . $theme . '/errors/' . $statusCode . '.twig';
        $defaultPath  = $baseViewsPath . 'default/errors/' . $statusCode . '.twig';

        if (file_exists($activePath)) {
            $templatePath = $activePath;
            $templateName = 'themes/' . $theme . '/errors/' . $statusCode . '.twig';
        } elseif (file_exists($defaultPath)) {
            $templatePath = $defaultPath;
            $templateName = 'themes/default/errors/' . $statusCode . '.twig';
        }

        // Try to get Twig environment from the container (already configured with theme paths)
        $twig = null;
        if (isset($GLOBALS['container']) && $GLOBALS['container']->has(\Twig\Environment::class)) {
            try {
                $twig = $GLOBALS['container']->resolve(\Twig\Environment::class);
            } catch (\Exception $err) {
                error_log('Cannot resolve Twig from container: ' . $err->getMessage());
            }
        }

        // If we have a template and Twig, render it
        if ($twig && $templatePath) {
            try {
                echo $twig->render($templateName, [
                    'message' => $statusCode === 404 ? 'Page not found.' : 'Server error.',
                    'code'    => $statusCode,
                ]);
                return;
            } catch (\Exception $twigError) {
                error_log('Twig error while rendering error page: ' . $twigError->getMessage());
            }
        }

        // Ultimate fallback: plain HTML (should rarely be used)
        $title       = $statusCode === 404 ? '404 Not Found' : '500 Internal Server Error';
        $bodyMessage = $statusCode === 404
            ? 'The page you requested could not be found.'
            : 'Something went wrong. Please try again later.';
        $baseUrl = $_ENV['APP_BASE_URL'] ?? '';

        echo "<!DOCTYPE html><html><head><title>{$title}</title><style>body{font-family:sans-serif;text-align:center;padding:50px;}</style></head>";
        echo "<body><h1>{$title}</h1><p>{$bodyMessage}</p>";
        echo "<a href='{$baseUrl}/'>Go Home</a></body></html>";
    }

    private static function getTwigForErrorPage(): ?\Twig\Environment
    {
        $theme    = self::$config['theme'] ?? 'default';
        $rootPath = dirname(__DIR__);
        $paths    = [];

        $activeThemePath = $rootPath . '/views/themes/' . $theme;
        if (is_dir($activeThemePath)) {
            $paths[] = $activeThemePath;
        }
        $defaultThemePath = $rootPath . '/views/themes/default';
        if ($activeThemePath !== $defaultThemePath && is_dir($defaultThemePath)) {
            $paths[] = $defaultThemePath;
        }

        if (empty($paths)) {
            error_log('No valid theme directories found for error page');
            return null;
        }

        $loader = new \Twig\Loader\FilesystemLoader($paths);
        $twig   = new \Twig\Environment($loader);
        $twig->addGlobal('base_url', $_ENV['APP_BASE_URL'] ?? '');
        $twig->addGlobal('app_env', $_ENV['APP_ENV'] ?? 'production');
        return $twig;
    }

    /**
     * Check if the application is in development mode.
     */
    private static function isDevelopment() : bool
    {
        return (self::$config['app_env'] ?? 'production') === 'development';
    }
}
