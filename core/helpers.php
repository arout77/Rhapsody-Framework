<?php

use Core\RedirectResponse;

if (! function_exists('redirect')) {
    function redirect(string $url): RedirectResponse
    {
        $baseUrl = $_ENV['APP_BASE_URL'] ?? '';
        return new RedirectResponse($baseUrl . $url);
    }
}

// --- NEW: Debugging Helpers ---

if (! function_exists('dd')) {
    /**
     * Dump one or more variables and stop script execution.
     *
     * @param mixed ...$vars
     * @return void
     */
    function dd(...$vars): void
    {
        foreach ($vars as $var) {
            dump($var); // Use Symfony's dump if available, otherwise var_dump
        }
        die(1);
    }
}

if (! function_exists('d')) {
    /**
     * Dump one or more variables (without stopping).
     *
     * @param mixed ...$vars
     * @return void
     */
    function d(...$vars): void
    {
        foreach ($vars as $var) {
            dump($var);
        }
    }
}

// Optional: Use Symfony VarDumper if installed (it usually is via Whoops)
if (! function_exists('dump') && class_exists('Symfony\Component\VarDumper\VarDumper')) {
    function dump(...$vars)
    {
        foreach ($vars as $var) {
            Symfony\Component\VarDumper\VarDumper::dump($var);
        }
    }
} elseif (! function_exists('dump')) {
    // Fallback to simple var_dump
    function dump(...$vars)
    {
        foreach ($vars as $var) {
            var_dump($var);
        }
    }
}
