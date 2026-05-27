<?php
namespace App\Controllers;

use App\Helpers\UrlHelper;
use App\Models\Videos;
use Core\BaseController;
use Core\Request;
use Core\Response;
use Twig\Environment;

class VideosController extends BaseController
{
    protected $urlhelper;

    public function __construct(Environment $twig)
    {
        parent::__construct($twig);
        $this->urlhelper = new UrlHelper();
    }

    /**
     * GET /videos (The Main Video Hub / Categories Page)
     */
    public function index(): Response
    {
        $videoModel = new Videos();
        $categories = $videoModel->getAllCategories();
        // Fetch unique types for the filter
        $testaments = $videoModel->getDistinctTestaments();

        return $this->view('videos/index.twig', [
            'categories' => $categories,
            'filters'    => $testaments,
        ]);
    }

    /**
     * GET /videos/{category_slug}
     */
    public function category(Request $request, string $category_slug): Response
    {
        $videoModel = new Videos();
        $category   = $videoModel->getCategoryBySlug($category_slug);

        if (! $category) {
            $response = new Response();
            $response->setStatusCode(404);
            $response->setContent($this->twig->render('errors/404.twig'));
            return $response;
        }

        $videos = $videoModel->getEpisodesByCategoryId($category['id']);

        // Points to: /views/themes/default/videos/video_category.twig
        return $this->view('videos/video_category.twig', [
            'category' => $category,
            'videos'   => $videos,
        ]);
    }

    /**
     * GET /videos/{category_slug}/{video_slug}
     */
    public function play(Request $request, string $category_slug, string $video_slug): Response
    {
        $videoModel = new Videos();
        $video      = $videoModel->getVideoWithCategoryContext($category_slug, $video_slug);

        // Standard 404 block if mismatch occurs or video is missing
        if (! $video) {
            $response = new Response();
            $response->setStatusCode(404);
            $response->setContent($this->twig->render('errors/404.twig'));
            return $response;
        }

        // Complies with your view path rule omitting 'themes/default/'
        // Maps to: /views/themes/default/videos/video_player.twig
        return $this->view('videos/video_player.twig', [
            'video' => $video,
        ]);
    }
}
