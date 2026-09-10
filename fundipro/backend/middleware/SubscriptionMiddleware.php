<?php
/**
 * Subscription Middleware - FundiPro
 * Enforces job limits based on subscription tier
 */
class SubscriptionMiddleware {
    private $db;

    public function __construct($db) { $this->db = $db; }

    public function handle($professionalId, $action = 'post_quote') {
        $sql = "SELECT subscription_tier, subscription_expires, total_jobs
                FROM professionals WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$professionalId]);
        $pro = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pro) return ['allowed' => false, 'message' => 'Professional not found.'];

        // Check subscription expiry
        if ($pro['subscription_tier'] !== 'free' && $pro['subscription_expires'] < date('Y-m-d')) {
            // Grace period check
            $expired = strtotime($pro['subscription_expires']);
            $graceDays = 3;
            if (time() > $expired + ($graceDays * 86400)) {
                $this->db->prepare(
                    "UPDATE professionals SET subscription_tier = 'free' WHERE id = ?"
                )->execute([$professionalId]);
                return ['allowed' => false, 'message' => 'Subscription expired. Please renew via M-Pesa.'];
            }
        }

        if ($pro['subscription_tier'] !== 'free') {
            return ['allowed' => true];
        }

        // Free tier: check monthly job limit (3 jobs)
        $sql = "SELECT COUNT(*) AS count FROM quotes q
                JOIN jobs j ON q.job_id = j.id
                WHERE q.professional_id = ? AND q.status = 'accepted'
                AND MONTH(j.created_at) = MONTH(NOW())
                AND YEAR(j.created_at) = YEAR(NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$professionalId]);
        $used = (int)$stmt->fetchColumn();

        if ($used >= 3) {
            return [
                'allowed' => false,
                'message' => 'Free plan limit reached (3 jobs/month). Upgrade to Pro for KES 500/month.',
                'upgrade_url' => 'https://fundipro.co.ke/pricing',
            ];
        }

        return ['allowed' => true, 'remaining' => 3 - $used];
    }
}
