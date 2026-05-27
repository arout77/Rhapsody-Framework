<?php
namespace App\Controllers;

use App\Models\Bible;
use Core\BaseController;
use Core\Database;
use Core\Pagination;
use Core\Request;
use Core\Response;
use Twig\Environment;

class BibleController extends BaseController
{

    public function __construct(Environment $twig)
    {
        parent::__construct($twig);
    }

    public function index(): Response
    {
        return $this->view('bible/index.twig');
    }

    /**
     * Resolves the active reading version dynamically
     */
    private function resolveActiveVersion(): string
    {
        // Default system fallback
        $version = 'kjv';

        // Check if an authenticated user session exists
        if (isset($_SESSION['user_id'])) {
            $db   = Database::getInstance();
            $stmt = $db->prepare("SELECT bible_version FROM user_preferences WHERE user_id = :uid LIMIT 1");
            $stmt->execute(['uid' => $_SESSION['user_id']]);
            $preference = $stmt->fetchColumn();

            if ($preference) {
                return $preference;
            }
        }

        // Check if explicit query parameter overrode selection (e.g., ?v=asv)
        if (isset($_GET['v']) && in_array($_GET['v'], ['kjv', 'asv', 'web', 'bbe', 'rv_1909'])) {
            return $_GET['v'];
        }

        return $version;
    }

    /**
     * Endpoint to save version updates via asynchronous fetch calls
     * Route: POST /user/preferences/bible
     */
    public function updateVersionPreference(Request $request): Response
    {
        $input   = json_decode(file_get_contents('php://input'), true);
        $version = $input['version'] ?? 'kjv';

        $allowed = ['kjv', 'asv', 'web', 'bbe', 'rv_1909'];
        if (! in_array($version, $allowed)) {
            return $this->json(['success' => false, 'error' => 'Invalid version designator'], 400);
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (! $userId) {
            return $this->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        $db   = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO user_preferences (user_id, bible_version)
            VALUES (:uid, :ver)
            ON DUPLICATE KEY UPDATE bible_version = :ver_update
        ");

        $success = $stmt->execute([
            'uid'        => $userId,
            'ver'        => $version,
            'ver_update' => $version,
        ]);

        return $this->json(['success' => $success]);
    }

    /**
     * Route: /bible/{book}/{chapter}
     * Displays the scripture reader view with dynamic version selections,
     * structural paragraph layout mappings, and full sidebar book indexes.
     */
    public function show(Request $request, string $book, string $chapter): Response
    {
        $bibleModel = new Bible();
        $bookSlug   = strtolower(trim($book));

        // 1. Fetch details for the active book to satisfy template metadata expectations
        $bookData = $bibleModel->getBookBySlug($bookSlug);

        if (! $bookData) {
            return $this->redirect('/bible/genesis/1');
        }

        $bookId         = (int) $bookData['id'];
        $currentChapter = (int) $chapter;
        $totalChapters  = $bibleModel->countChapters($bookId);

        // Sanity check for chapter boundaries
        if ($currentChapter < 1 || $currentChapter > $totalChapters) {
            return $this->redirect("/bible/{$bookSlug}/1");
        }

        $chaptersPerPage = 1;
        $pagination      = new Pagination($totalChapters, $chaptersPerPage, $currentChapter);

        // 2. Resolve the targeted version prefix context (e.g., 'kjv', 'asv', 'web')
        $activeVersion = $this->resolveActiveVersion();

        // 3. FIXED: Swap out the paginated query limit for the complete chapter fetcher
        $rawVerses = $bibleModel->getWholeChapterByVersion(
            $bookId,
            $currentChapter,
            $activeVersion
        );

        // 4. Parse scriptural verses into structured paragraphs matching view expectations
        $paragraphs       = [];
        $currentParagraph = [];

        foreach ($rawVerses as $verse) {
            $rawText = $verse['text'];

            // Check for the pilcrow character to split blocks cleanly
            if (str_contains($rawText, '¶')) {
                if (! empty($currentParagraph)) {
                    $paragraphs[]     = $currentParagraph;
                    $currentParagraph = [];
                }
                // Strip the formatting mark out of the visible user string
                $verse['text'] = trim(str_replace('¶', '', $rawText));
            }
            $currentParagraph[] = $verse;
        }

        if (! empty($currentParagraph)) {
            $paragraphs[] = $currentParagraph;
        }

        // 5. Retrieve all canonical books to construct the persistent sidebar listing navigation
        $allBooks = $bibleModel->getAllBooks();

        // 6. Bind parameters safely to feed template layout hooks cleanly
        return $this->view('bible/reader.twig', [
            'books'           => $allBooks,       // Populates the left-hand navigation sidebar
            'book'            => $bookData,       // Maps to {{ book.name }} and details in header bars
            'paragraphs'      => $paragraphs,     // Nested collection of verses grouped into textual sections
            'current_book'    => $bookSlug,       // Used for path synchronization checks
            'current_chapter' => $currentChapter, // Sets active tab numbers and state selections
            'total_chapters'  => $totalChapters,  // Calculates relative boundary markers
            'active_version'  => $activeVersion,  // Synchronizes the navbar dropdown components
        ]);
    }

    /**
     * Intercepts literal pilcrow marks on the fly, partitions structural blocks,
     * and strips the marker symbol so it is completely invisible in the frontend.
     */
    private function groupVersesIntoParagraphs(array $verses): array
    {
        $paragraphs       = [];
        $currentParagraph = [];

        foreach ($verses as $verse) {
            $rawText = $verse['text'];

            if (str_contains($rawText, '¶')) {
                if (! empty($currentParagraph)) {
                    $paragraphs[]     = $currentParagraph;
                    $currentParagraph = [];
                }
                // Strip out the symbol from the rendered presentation layer
                $verse['text'] = trim(str_replace('¶', '', $rawText));
            }

            $currentParagraph[] = $verse;
        }

        if (! empty($currentParagraph)) {
            $paragraphs[] = $currentParagraph;
        }

        return $paragraphs;
    }

    /**
     * Handles Bible Search
     * Route: /bible/search
     */
    public function search(Request $request): Response
    {
        $query = $request->getQueryParam('q', '');

        if (strlen($query) < 3) {
            return $this->json([]);
        }

        $db   = Database::getInstance();
        $stmt = $db->prepare("
            SELECT book_name, chapter, verse, text
            FROM bible_verses_kjv
            WHERE text LIKE :q
            LIMIT 20
        ");

        $stmt->execute(['q' => "%$query%"]);
        return $this->json($stmt->fetchAll());
    }

    /**
     * Fetches a random verse for the Trivia module
     * Route: /bible/trivia/random
     */
    public function random(Request $request): Response
    {
        $db = Database::getInstance();
        // Fetch one random verse for a "Who said this?" or "Complete the verse" challenge
        $stmt = $db->query("SELECT book_name, chapter, verse, text FROM bible_verses_kjv ORDER BY RAND() LIMIT 1");

        return $this->json($stmt->fetch());
    }
}
