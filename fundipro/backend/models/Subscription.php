<?php
/**
 * Subscription Model - FundiPro
 * KES-based subscription tiers: Free, Pro (500), Business (1200)
 */
class Subscription {
    private $db;

    const PLANS = [
        'free'     => ['price' => 0,    'jobs' => 3,    'label' => 'Free'],
        'pro'      => ['price' => 500,  'jobs' => null, 'label' => 'Pro - KES 500/month'],
        'business' => ['price' => 1200, 'jobs' => null, 'label' => 'Business - KES 1,200/month'],
    ];

    public function __construct($db) { $this->db = $db; }

    public function subscribe($professionalId, $plan, $mpesaTransactionId) {
        if (!array_key_exists($plan, self::PLANS)) {
            throw new Exception("Invalid plan: $plan");
        }
        $today   = date('Y-m-d');
        $expires = date('Y-m-d', strtotime('+1 month'));

        $sql = "INSERT INTO subscriptions
                (professional_id, plan, amount, status, starts_at, expires_at, mpesa_transaction_id)
                VALUES (?, ?, ?, 'active', ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $professionalId,
            $plan,
            self::PLANS[$plan]['price'],
            $today,
            $expires,
            $mpesaTransactionId,
        ]);

        // Update professional tier
        $this->db->prepare(
            "UPDATE professionals SET subscription_tier = ?, subscription_expires = ? WHERE id = ?"
        )->execute([$plan, $expires, $professionalId]);

        return $this->db->lastInsertId();
    }

    public function getExpiringSoon($days = 3) {
        $date = date('Y-m-d', strtotime("+$days days"));
        $sql  = "SELECT s.*, p.id AS pro_id, u.phone, u.first_name
                 FROM subscriptions s
                 JOIN professionals p ON s.professional_id = p.id
                 JOIN users u ON p.user_id = u.id
                 WHERE s.status = 'active' AND s.expires_at <= ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function expire($id) {
        $this->db->prepare("UPDATE subscriptions SET status = 'expired' WHERE id = ?")->execute([$id]);
        // Downgrade professional to free
        $sub = $this->db->prepare("SELECT professional_id FROM subscriptions WHERE id = ?");
        $sub->execute([$id]);
        $data = $sub->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $this->db->prepare(
                "UPDATE professionals SET subscription_tier = 'free', subscription_expires = NULL WHERE id = ?"
            )->execute([$data['professional_id']]);
        }
    }
}
