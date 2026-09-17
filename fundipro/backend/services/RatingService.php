<?php
/**
 * Rating Service - FundiPro
 * Wilson score rating calculation for professionals
 */
class RatingService {
    private $db;

    public function __construct($db) { $this->db = $db; }

    public function recalculate($professionalId) {
        $sql = "SELECT COUNT(*) AS n,
                       SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) AS positive,
                       AVG(rating) AS avg_rating
                FROM reviews WHERE professional_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$professionalId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $n        = (int)$data['n'];
        $avg      = round((float)$data['avg_rating'], 2);
        $score    = $n >= 5 ? $this->wilsonScore((int)$data['positive'], $n) : $avg;

        $this->db->prepare(
            "UPDATE professionals SET rating = ?, total_reviews = ? WHERE id = ?"
        )->execute([$score, $n, $professionalId]);

        return ['rating' => $score, 'total_reviews' => $n];
    }

    private function wilsonScore($positive, $total, $confidence = 1.96) {
        if ($total === 0) return 0;
        $p = $positive / $total;
        $n = $total;
        $z = $confidence;
        $left  = $p + $z * $z / (2 * $n);
        $right = $z * sqrt($p * (1 - $p) / $n + $z * $z / (4 * $n * $n));
        $under = 1 + $z * $z / $n;
        return round(($left - $right) / $under * 5, 2);
    }
}
