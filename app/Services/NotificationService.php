<?php

namespace App\Services;

use Core\Cache;
use Core\Response;

class NotificationService
{
    /**
     * Injects an update notification banner into an HTML response if an update is available.
     *
     * @param Response $response The response object before it's sent.
     * @return Response The (potentially modified) response object.
     */
    public function injectBanner( Response $response ): Response
    {
        // 1. Check the cache for an available update
        $newVersion = Cache::get( 'update_available' );

        if ( !$newVersion )
        {
            return $response; // No update, do nothing
        }

        $content = $response->getContent();

        // 2. Only inject into full HTML pages
        if ( !str_contains( $content, '</body>' ) )
        {
            return $response;
        }

        // 3. Create the HTML for the banner
        // This is styled with inline styles to be independent of any CSS file
        $bannerHtml = $this->createBannerHtml( $newVersion );

        // 4. Inject the banner before the closing </body> tag
        $modifiedContent = str_ireplace( '</body>', $bannerHtml . '</body>', $content );
        $response->setContent( $modifiedContent );

        return $response;
    }

    /**
     * @param string $version
     */
    private function createBannerHtml( string $version ): string
    {
        // Using inline styles to ensure the banner is always visible and styled correctly
        return <<<HTML
            <div id="rhapsody-update-banner" style="position: fixed; bottom: 0; left: 0; width: 100%; background-color: #1F2937; color: #F9FAFB; padding: 12px; z-index: 99999; display: flex; justify-content: center; align-items: center; font-family: sans-serif; font-size: 14px; border-top: 1px solid #4B5563;">
                <span class="animate-pulse bg-pink-700" style="font-weight: bold; font-size: 10px; padding: 2px 8px; border-radius: 9999px; margin-right: 12px;">UPDATE</span>
                A new version of Rhapsody is available! &nbsp;<strong>($version)</strong>
                <a href="#" id="update-notification-link" data-version="$version" style="color: #60A5FA; font-weight: bold; text-decoration: underline; margin-left: 16px;">View Details & Release Notes</a>
            </div>
        HTML;
    }
}
