<?php
/**
 * Review Model - FundiPro
 * Customer reviews of professionals - verified purchases only
 */
class Review {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function create($data) {
        // Only allow review after completed paid job
        $check = $this->db->prepare(
            "SELECT j.id FROM jobs j
             JOIN quotes q ON q.job_id = j.id
             WHERE j.id = ? AND j.customer_id = ?
             AND j.status = 'completed' AND q.professional_id = ?"
        );
        $check->execute([$data['job_id'], $data['customer_id'], $data['professional_id']]);
        if (!$check->fetch()) {
            throw new Exception('You can only review after a completed job.');
        }

        // One review per job
        $dup = $this->db->prepare("SELECT id FROM reviews WHERE job_id = ? AND customer_id = ?");
        $dup->execute([$data['job_id'], $data['customer_id']]);
        if ($dup->fetch()) {
            throw new Exception('You have already reviewed this job.');
        }

        $sql = "INSERT INTO reviews (job_id, customer_id, professional_id, rating, comment)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['job_id'],
            $data['customer_id'],
            $data['professional_id'],
            $data['rating'],
            $data['comment'] ?? null,
        ]);

        // Recalculate professional rating
        $this->updateProfessionalRating($data['professional_id']);
        return $this->db->lastInsertId();
    }

    public function getByProfessional($professionalId, $limit = 10) {
        $sql = "SELECT r.*, u.first_name, u.last_name, j.title AS job_title
                FROM reviews r
                JOIN users u ON r.customer_id = u.id
                JOIN jobs j ON r.job_id = j.id
                WHERE r.professional_id = ?
                ORDER BY r.created_at DESC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$professionalId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function updateProfessionalRating($professionalId) {
        $sql = "UPDATE professionals
                SET rating = (SELECT AVG(rating) FROM reviews WHERE professional_id = ?),
                    total_reviews = (SELECT COUNT(*) FROM reviews WHERE professional_id = ?)
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$professionalId, $professionalId, $professionalId]);
    }
}
