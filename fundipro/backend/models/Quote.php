<?php
/**
 * Quote Model - FundiPro
 * Professional quotes on customer jobs in KES
 */
class Quote {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function create($data) {
        // Check fundi hasn't already quoted this job
        $check = $this->db->prepare(
            "SELECT id FROM quotes WHERE job_id = ? AND professional_id = ?"
        );
        $check->execute([$data['job_id'], $data['professional_id']]);
        if ($check->fetch()) {
            throw new Exception('You have already submitted a quote for this job.');
        }

        $sql = "INSERT INTO quotes
                (job_id, professional_id, amount, labor_cost, materials_cost, description, valid_until)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['job_id'],
            $data['professional_id'],
            $data['amount'],          // Total in KES
            $data['labor_cost'] ?? null,
            $data['materials_cost'] ?? null,
            $data['description'] ?? null,
            $data['valid_until'] ?? date('Y-m-d', strtotime('+7 days')),
        ]);

        // Update job status to 'quoted'
        $this->db->prepare("UPDATE jobs SET status = 'quoted' WHERE id = ? AND status = 'open'")
                 ->execute([$data['job_id']]);

        return $this->db->lastInsertId();
    }

    public function getByJobId($jobId) {
        $sql = "SELECT q.*, p.rating, p.total_jobs, p.verification_status,
                       u.first_name, u.last_name, u.phone
                FROM quotes q
                JOIN professionals p ON q.professional_id = p.id
                JOIN users u ON p.user_id = u.id
                WHERE q.job_id = ? AND q.status = 'pending'
                ORDER BY q.amount ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$jobId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function accept($quoteId, $customerId) {
        // Verify customer owns the job
        $sql = "SELECT q.id, q.job_id, q.professional_id FROM quotes q
                JOIN jobs j ON q.job_id = j.id
                WHERE q.id = ? AND j.customer_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$quoteId, $customerId]);
        $quote = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$quote) throw new Exception('Quote not found or access denied.');

        // Accept this quote, reject others
        $this->db->prepare("UPDATE quotes SET status = 'accepted' WHERE id = ?")->execute([$quoteId]);
        $this->db->prepare(
            "UPDATE quotes SET status = 'rejected' WHERE job_id = ? AND id != ?"
        )->execute([$quote['job_id'], $quoteId]);

        // Update job status
        $this->db->prepare("UPDATE jobs SET status = 'booked' WHERE id = ?")
                 ->execute([$quote['job_id']]);

        return $quote['professional_id'];
    }
}
