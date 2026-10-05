<?php
/**
 * Admin Controller - FundiPro
 * Platform administration: users, verification, disputes, analytics
 */
class AdminController {
    private $db;

    public function __construct($db) { $this->db = $db; }

    public function getDashboardStats() {
        return [
            'total_professionals' => $this->count("SELECT COUNT(*) FROM professionals WHERE verification_status = 'verified'"),
            'total_customers'     => $this->count("SELECT COUNT(*) FROM users WHERE role = 'customer'"),
            'jobs_this_month'     => $this->count("SELECT COUNT(*) FROM jobs WHERE MONTH(created_at) = MONTH(NOW())"),
            'revenue_this_month'  => $this->sum("SELECT SUM(amount) FROM payments WHERE status = 'completed' AND MONTH(created_at) = MONTH(NOW())"),
            'pending_verifications' => $this->count("SELECT COUNT(*) FROM professionals WHERE verification_status = 'pending'"),
            'active_subscriptions'  => $this->count("SELECT COUNT(*) FROM subscriptions WHERE status = 'active'"),
        ];
    }

    public function getVerificationQueue() {
        $sql = "SELECT p.id, p.national_id, p.id_photo_url, p.selfie_url,
                       p.certificate_urls, p.verification_status,
                       u.first_name, u.last_name, u.phone, p.trades
                FROM professionals p
                JOIN users u ON p.user_id = u.id
                WHERE p.verification_status = 'pending'
                ORDER BY p.updated_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function suspendUser($userId, $reason) {
        $this->db->prepare(
            "UPDATE users SET is_active = 0 WHERE id = ?"
        )->execute([$userId]);
        $this->db->prepare(
            "INSERT INTO admin_actions (user_id, action, reason, created_at) VALUES (?, 'suspend', ?, NOW())"
        )->execute([$userId, $reason]);
    }

    public function getSubscriptionRevenue($months = 6) {
        $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month,
                       plan, COUNT(*) AS count, SUM(amount) AS revenue
                FROM subscriptions
                WHERE status IN ('active','expired')
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
                GROUP BY month, plan
                ORDER BY month DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$months]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function count($sql) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    private function sum($sql) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return 'KES ' . number_format((float)$stmt->fetchColumn(), 2);
    }
}
