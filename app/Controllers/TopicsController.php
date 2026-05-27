<?php
namespace App\Controllers;

use Core\BaseController;
use Core\Request;
use Core\Response;
use PDO;

class TopicsController extends BaseController
{
    /**
     * GET /navigating-scripture/{group_slug}/{topic_slug}
     */
    public function show(Request $request, string $group_slug, string $topic_slug): Response
    {
        // 1. Sanitize input segments to protect against directory traversal
        $safeGroup = preg_replace('/[^a-zA-Z0-9_-]/', '', $group_slug);
        $safeTopic = preg_replace('/[^a-zA-Z0-9_-]/', '', $topic_slug);

        // 2. Query the DB to check the relationship AND grab the presentation names
        $sql = "SELECT t.name AS topic_name, g.name AS group_name
                FROM scripture_topics t
                INNER JOIN scripture_groups g ON t.group_id = g.id
                WHERE g.slug = :group_slug AND t.slug = :topic_slug
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'group_slug' => $safeGroup,
            'topic_slug' => $safeTopic,
        ]);

        $pageData = $stmt->fetch(PDO::FETCH_ASSOC);

        // 3. Fallback 404 if the slugs do not map together in the database
        if (! $pageData) {
            return $this->abort404();
        }

        // 4. Verify the physical Twig file exists on disk
        $expectedTemplate = "navigating_scripture/topics/{$safeTopic}.twig";
        $absolutePath     = dirname(__DIR__, 2) . "/views/themes/default/" . $expectedTemplate;

        if (! file_exists($absolutePath)) {
            return $this->abort404();
        }

        // 5. Dynamically compile optimal SEO Metadata
        $meta = [
            'title' => "{$pageData['topic_name']} - {$pageData['group_name']} | Faith Project",
            'description' => "Deep dive into scripture, biblical context, and spiritual guidance regarding {$pageData['topic_name']} in our {$pageData['group_name']} study series.",
        ];

        // 6. Pass data and metadata array right into Rhapsody's view layout layer
        return $this->view($expectedTemplate, [], $meta);
    }

    private function abort404(): Response
    {
        $response = new Response();
        $response->setStatusCode(404);
        $response->setContent($this->twig->render('errors/404.twig'));
        return $response;
    }
}
