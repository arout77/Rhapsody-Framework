<?php
namespace App\Models;

use Core\BaseModel;
use PDO;

class Videos extends BaseModel
{
    protected string $table = 'bible_videos';

    /**
     * Fetch all categories to populate the main video hub grid along with counts
     */
    public function getAllCategories(): array
    {
        $sql = "SELECT vc.*, COUNT(bv.id) AS video_count
                FROM video_categories vc
                LEFT JOIN bible_videos bv ON vc.id = bv.category_id
                GROUP BY vc.id
                ORDER BY vc.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Fetch a video category by its slug
     */
    public function getCategoryBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM video_categories WHERE slug = :slug LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result : null;
    }

    /**
     * Fetch a specific video while confirming its parent category slug context
     */
    public function getVideoWithCategoryContext(string $categorySlug, string $videoSlug): ?array
    {
        $query = "SELECT v.*, c.name AS category_name, c.slug AS category_slug
                  FROM bible_videos v
                  INNER JOIN video_categories c ON v.category_id = c.id
                  WHERE c.slug = :category_slug AND v.slug = :video_slug
                  LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'category_slug' => $categorySlug,
            'video_slug'    => $videoSlug,
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result : null;
    }

    /**
     * Fetch all individual video rows belonging to a category group ID context
     */
    public function getEpisodesByCategoryId(int $categoryId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM bible_videos WHERE category_id = :category_id ORDER BY id ASC");
        $stmt->execute(['category_id' => $categoryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch unique testament types to populate dynamic filter buttons
     */
    public function getDistinctTestaments(): array
    {
        $sql  = "SELECT DISTINCT testament FROM video_categories WHERE testament IS NOT NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        // Returns an array of testament values, e.g., ['Old Testament', 'New Testament', 'General']
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}
