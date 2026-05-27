<?php
namespace App\Models;

use Core\BaseModel;
use PDO;

class Trivia extends BaseModel
{
    protected string $table = 'bible_trivia';

    /**
     * Fetch a list of trivia questions with an optional limit and difficulty filter
     */
    public function getQuestions(int $limit = 10, ?string $difficulty = null): array
    {
        $sql = "SELECT id, question, option_a, option_b, option_c, option_d, correct_option, explanation, difficulty
                FROM {$this->table}";

        $params = [];

        if ($difficulty !== null) {
            $sql                   .= " WHERE difficulty = :difficulty";
            $params[':difficulty']  = $difficulty;
        }

        $sql .= " ORDER BY RAND() LIMIT :limit";

        $stmt  = $this->db->prepare($sql);

        // Bind the parameters securely
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Placeholder: Save a user's trivia performance score
     * Ready for implementation when user tracking becomes a priority.
     */
    public function saveUserScore(string $userId, int $score, int $totalQuestions): bool
    {
        // For future implementation:
        // $stmt = $this->db->prepare("INSERT INTO user_trivia_history (user_id, score, total_questions, played_at) VALUES (...)");
        return true;
    }
}
