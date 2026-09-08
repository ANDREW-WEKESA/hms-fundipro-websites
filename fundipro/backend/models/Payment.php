<?php
/**
 * Payment Model - FundiPro
 * M-Pesa payment records in KES
 */
class Payment {
    private $db;

    public function __construct($db) { $this->db = $db; }

    public function create($data) {
        $sql = "INSERT INTO payments
                (payer_id, recipient_id, job_id, subscription_id,
                 amount, type, method, mpesa_phone, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['payer_id'],
            $data['recipient_id'] ?? null,
            $data['job_id'] ?? null,
            $data['subscription_id'] ?? null,
            $data['amount'],
            $data['type'],
            $data['method'] ?? 'mpesa',
            $data['mpesa_phone'] ?? null,
        ]);
        return $this->db->lastInsertId();
    }

    public function confirm($id, $mpesaTransactionId) {
        $stmt = $this->db->prepare(
            "UPDATE payments SET status = 'completed', mpesa_transaction_id = ? WHERE id = ?"
        );
        return $stmt->execute([$mpesaTransactionId, $id]);
    }

    public function getEarnings($professionalId, $month = null) {
        $sql = "SELECT SUM(p.amount) AS total, COUNT(*) AS count
                FROM payments p
                JOIN jobs j ON p.job_id = j.id
                JOIN quotes q ON q.job_id = j.id
                WHERE q.professional_id = ? AND p.status = 'completed'";
        $params = [$professionalId];
        if ($month) {
            $sql .= " AND DATE_FORMAT(p.created_at, '%Y-%m') = ?";
            $params[] = $month;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
