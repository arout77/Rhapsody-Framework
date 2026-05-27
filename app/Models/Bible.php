<?php
namespace App\Models;

use Core\BaseModel;
use PDO;

class Bible extends BaseModel
{
    // Strict lookup map translating friendly URL slugs back to the 'b' column integer ID
    protected static array $bookMap = [
        'genesis'      => 1, 'exodus'           => 2, 'leviticus'        => 3, 'numbers'          => 4, 'deuteronomy'   => 5,
        'joshua'       => 6, 'judges'           => 7, 'ruth'             => 8, '1-samuel'         => 9, '2-samuel'      => 10,
        '1-kings'      => 11, '2-kings'         => 12, '1-chronicles'    => 13, '2-chronicles'    => 14, 'ezra'         => 15,
        'nehemiah'     => 16, 'esther'          => 17, 'job'             => 18, 'psalms'          => 19, 'proverbs'     => 20,
        'ecclesiastes' => 21, 'song-of-solomon' => 22, 'isaiah'          => 23, 'jeremiah'        => 24, 'lamentations' => 25,
        'ezekiel'      => 26, 'daniel'          => 27, 'hosea'           => 28, 'joel'            => 29, 'amos'         => 30,
        'obadiah'      => 31, 'jonah'           => 32, 'micah'           => 33, 'nahum'           => 34, 'habakkuk'     => 35,
        'zephaniah'    => 36, 'haggai'          => 37, 'zechariah'       => 38, 'malachi'         => 39,
        'matthew'      => 40, 'mark'            => 41, 'luke'            => 42, 'john'            => 43, 'acts'         => 44,
        'romans'       => 45, '1-corinthians'   => 46, '2-corinthians'   => 47, 'galatians'       => 48, 'ephesians'    => 49,
        'philippians'  => 50, 'colossians'      => 51, '1-thessalonians' => 52, '2-thessalonians' => 53, '1-timothy'    => 54,
        '2-timothy'    => 55, 'titus'           => 56, 'philemon'        => 57, 'hebrews'         => 58, 'james'        => 59,
        '1-peter'      => 60, '2-peter'         => 61, '1-john'          => 62, '2-john'          => 63, '3-john'       => 64,
        'jude'         => 65, 'revelation'      => 66,
    ];

    /**
     * Translates a clean, low-case text slug back to its database book identifier.
     * Resolves purely via the static mapping dictionary.
     * @param string $slug
     * @return int|null
     */
    public function getBookId(string $slug): ?int
    {
        $cleanSlug = strtolower(trim($slug));
        return self::$bookMap[$cleanSlug] ?? null;
    }

    /**
     * Fetches details for a specific book by its slug.
     * Uses the static map to format basic book arrays to satisfy view parameters cleanly.
     * @param string $slug
     * @return array|null
     */
    public function getBookBySlug(string $slug): ?array
    {
        $cleanSlug = strtolower(trim($slug));
        $bookId    = $this->getBookId($cleanSlug);

        if (! $bookId) {
            return null;
        }

        // Returns formatted metadata title capitalizing slugs cleanly (or override via a secondary mapper array)
        return [
            'id'   => $bookId,
            'slug' => $cleanSlug,
            'name' => ucwords(str_replace('-', ' ', $cleanSlug)),
        ];
    }

    /**
     * Retrieves all structural books indexed by their physical biblical canonical sequences.
     * Required to cleanly populate your left-hand reader navigation sidebar loop.
     * @return array
     */
    public function getAllBooks(): array
    {
        $booksList = [];
        foreach (self::$bookMap as $slug => $id) {
            $booksList[] = [
                'id'   => $id,
                'slug' => $slug,
                'name' => ucwords(str_replace('-', ' ', $slug)),
            ];
        }

        // Sort by canonical ID sequence order
        usort($booksList, function ($a, $b) {
            return $a['id'] <=> $b['id'];
        });

        return $booksList;
    }

    /**
     * Counts the maximum number of unique chapters contained within a selected book entity.
     * Queries dynamically based on verse indices found in standard translation collections.
     * @param int $bookId
     * @return int
     */
    public function countChapters(int $bookId): int
    {
        // Counts chapters by scanning table structural footprints cleanly
        $stmt = $this->db->prepare("SELECT COUNT(DISTINCT chapter) FROM `bible_verses_kjv` WHERE book = :book_id");
        $stmt->execute(['book_id' => $bookId]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Legacy Single-Version Fetcher (KJV Fallback Track)
     * Keeps backward compatibility intact for sections not yet utilizing the translation switcher.
     * @param int $bookId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findPaginatedChapter(int $bookId, int $limit, int $offset): array
    {
        return $this->findPaginatedChapterByVersion($bookId, 'kjv', $limit, $offset);
    }

    /**
     * Core Multi-Version Dynamic Query Engine
     * Fetches verses for a given book and chapter dynamically from the specified translation table.
     * Handles schema structures cleanly (e.g. bible_verses_kjv, bible_verses_asv, etc.)
     * @param int $bookId
     * @param string $version
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findPaginatedChapterByVersion(int $bookId, string $version, int $limit, int $offset): array
    {
        // Guard rails: Map dynamic version string into verified, whitelisted schema tables
        $allowedVersions = ['kjv', 'asv', 'web', 'bbe', 'rv_1909'];
        $cleanVersion    = in_array($version, $allowedVersions) ? $version : 'kjv';

        // Translate shortcut tag into physical naming structures found inside database layouts
        $tableName = "bible_verses_" . $cleanVersion;

        // Construct clean queries matching your table structures
        $sql = "SELECT id, book, book_name, chapter, verse, text
                FROM `{$tableName}`
                WHERE book = :book_id
                ORDER BY verse ASC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetches all verses for a specific book and chapter dynamically from the version table.
     * Bypasses verse limits so the entire scriptural chapter displays at once.
     * @param int $bookId
     * @param int $chapter
     * @param string $version
     * @return array
     */
    public function getWholeChapterByVersion(int $bookId, int $chapter, string $version): array
    {
        $allowedVersions = ['kjv', 'asv', 'web', 'bbe', 'rv_1909'];
        $cleanVersion    = in_array($version, $allowedVersions) ? $version : 'kjv';
        $tableName       = "bible_verses_" . $cleanVersion;

        $sql = "SELECT id, book, book_name, chapter, verse, text
                FROM `{$tableName}`
                WHERE book = :book_id AND chapter = :chapter
                ORDER BY verse ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':book_id', $bookId, \PDO::PARAM_INT);
        $stmt->bindValue(':chapter', $chapter, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Handles Bible Search using Full-Text Indexes or Text-Matching.
     * Leverages the custom translation parameter to search within their chosen version.
     * @param string $query
     * @param string $version Defaults to KJV if not specified
     * @return array
     */
    public function searchVerses(string $query, string $version = 'kjv'): array
    {
        $allowedVersions = ['kjv', 'asv', 'web', 'bbe', 'rv_1909'];
        $cleanVersion    = in_array($version, $allowedVersions) ? $version : 'kjv';
        $tableName       = "bible_verses_" . $cleanVersion;

        $stmt = $this->db->prepare("
            SELECT book_name, chapter, verse, text
            FROM `{$tableName}`
            WHERE text LIKE :q
            LIMIT 20
        ");

        $stmt->execute(['q' => "%{$query}%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetches a single random verse across the scripture index.
     * Primarily used to supply your Trivia engine components with dynamic content variations.
     * @param string $version Defaults to KJV if not specified
     * @return array|null
     */
    public function getRandomVerse(string $version = 'kjv'): ?array
    {
        $allowedVersions = ['kjv', 'asv', 'web', 'bbe', 'rv_1909'];
        $cleanVersion    = in_array($version, $allowedVersions) ? $version : 'kjv';
        $tableName       = "bible_verses_" . $cleanVersion;

        // Optimized random picker leveraging total table index footprint caps
        $countStmt = $this->db->query("SELECT MAX(id) FROM `{$tableName}`");
        $maxId     = (int) $countStmt->fetchColumn();

        if ($maxId === 0) {
            return null;
        }

        $randomId = rand(1, $maxId);

        $stmt = $this->db->prepare("
            SELECT id, book, book_name, chapter, verse, text
            FROM `{$tableName}`
            WHERE id >= :random_id
            LIMIT 1
        ");

        $stmt->bindValue(':random_id', $randomId, PDO::PARAM_INT);
        $stmt->execute();
        $verse = $stmt->fetch(PDO::FETCH_ASSOC);

        return $verse ?: $this->getRandomVerse($version);
    }
}
