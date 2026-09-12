<?php
/**
 * Booking Model - FundiPro
 * Confirmed job bookings after quote acceptance
 */
class Booking {
    private $db;

    const STATUSES = ['confirmed', 'in_progress', 'completed', 'cancelled', 'disputed'];

    public function __construct($db) { $this->db = $db; }

    public function createFromQuote($quoteId) {
        $sql = "SELECT q.*, j.customer_id, j.preferred_date
                FROM quotes q JOIN jobs j ON q.job_id = j.id
                WHERE q.id = ? AND q.status = 'accepted'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$quoteId]);
        $quote = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$quote) throw new Exception('Quote not found or not accepted.');

        $insert = $this->db->prepare(
            "INSERT INTO bookings (job_id, quote_id, professional_id, customer_id,
             scheduled_date, amount, status)
             VALUES (?, ?, ?, ?, ?, ?, 'confirmed')"
        );
        $insert->execute([
            $quote['job_id'], $quoteId,
            $quote['professional_id'], $quote['customer_id'],
            $quote['preferred_date'], $quote['amount'],
        ]);
        return $this->db->lastInsertId();
    }

    public function complete($id, $customerId) {
        $stmt = $this->db->prepare(
            "UPDATE bookings SET status = 'completed', completed_at = NOW()
             WHERE id = ? AND customer_id = ? AND status = 'in_progress'"
        );
        $stmt->execute([$id, $customerId]);
        // Update job status
        $job = $this->db->prepare("SELECT job_id FROM bookings WHERE id = ?");
        $job->execute([$id]);
        $data = $job->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $this->db->prepare("UPDATE jobs SET status = 'completed' WHERE id = ?")
                     ->execute([$data['job_id']]);
        }
        return $stmt->rowCount() > 0;
    }
}
