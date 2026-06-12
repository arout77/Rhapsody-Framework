<?php
namespace Core;

/**
 * A secure, static wrapper for handling PHP sessions.
 *
 * Provides a consistent and safe interface for managing session data,
 * including user authentication state, flash messages, and CSRF tokens.
 */
class Session
{
    /**
     * Starts the session if it has not already been started.
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Age out old flash data from the PREVIOUS request
        if (isset($_SESSION['_flash_old'])) {
            foreach ($_SESSION['_flash_old'] as $key) {
                unset($_SESSION[$key]);
            }
        }

        // 2. Mark current flashes to be deleted on the NEXT request
        $_SESSION['_flash_old'] = isset($_SESSION['_flash_new']) ? $_SESSION['_flash_new'] : [];
        $_SESSION['_flash_new'] = [];
    }

    public static function flash(string $key, mixed $message): void
    {
        $_SESSION[$key]           = $message;
        $_SESSION['_flash_new'][] = $key;
    }

    /**
     * Sets a value in the session.
     */
    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Retrieves a value from the session by its key.
     */
    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Checks if a key exists in the session.
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Removes a value from the session by its key.
     */
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Fully destroys the session: clears session data, expires the cookie,
     * and calls session_destroy(). Without all three steps the client retains
     * a valid cookie and $_SESSION remains populated for the rest of the request.
     */
    public static function destroy(): void
    {
        // 1. Clear the server-side session data immediately.
        $_SESSION = [];

        // 2. Expire the session cookie in the browser.
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // 3. Destroy the session on the server.
        session_destroy();
    }

    /**
     * Regenerates the session ID to prevent session fixation attacks.
     * Call immediately after a user logs in.
     */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    /**
     * Writes session data to disk and closes the session.
     * Call before any redirects to ensure data is saved.
     */
    public static function close(): void
    {
        session_write_close();
    }

    /**
     * Retrieves a flash message and then removes it from the session.
     */
    public static function getFlash(string $key, $default = null): ?string
    {
        $message = self::get('flash_' . $key, $default);
        self::remove('flash_' . $key);
        return $message;
    }

    /**
     * Checks if a flash message exists for a given key.
     */
    public static function hasFlash(string $key): bool
    {
        return self::has('flash_' . $key);
    }

    /**
     * Generates and returns a CSRF token, creating one if it doesn't exist.
     */
    public static function csrfToken(): string
    {
        if (! self::has('_token')) {
            $token = bin2hex(random_bytes(32));
            self::set('_token', $token);
        }
        return self::get('_token');
    }

    /**
     * Verifies a submitted CSRF token against the one stored in the session.
     * Uses hash_equals for timing-attack-safe comparison.
     */
    public static function verifyCsrfToken(string $token): bool
    {
        return self::has('_token') && hash_equals(self::get('_token'), $token);
    }
}
