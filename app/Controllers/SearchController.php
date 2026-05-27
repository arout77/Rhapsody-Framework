<?php
namespace App\Controllers;

use App\Models\Bible;
use Core\BaseController;
use Core\Request;
use Core\Response;

class SearchController extends BaseController
{
    /**
     * Handles Bible Search
     *
     * ---- UPDATE: this is actually being handled in the Bible controller ----
     * Will either move that here in the future, or use this for a different
     * search function (searching commentaries, or something else)
     *
     */
    public function search(Request $request): Response
    {
        $query = $request->getQueryParam('q', '');

        // Basic validation guard to prevent scanning on short keywords
        if (strlen(trim($query)) < 3) {
            return $this->json([]);
        }

        try {
            $bibleModel = new Bible();
            $results    = $bibleModel->searchVerses($query);

            return $this->json($results);
        } catch (\Exception $e) {
            // Safe JSON fallback configuration inside the catch tracking wrapper
            $response = new Response();
            $response->setStatusCode(500);
            $response->setHeader('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Internal Search Engine Error: ' . $e->getMessage(),
            ]));
            return $response;
        }
    }

    /**
     * Fetches a random verse for the Trivia module
     */
    public function random(Request $request): Response
    {
        $bibleModel = new Bible();
        $verse      = $bibleModel->getRandomVerse();

        return $this->json($verse);
    }
}
