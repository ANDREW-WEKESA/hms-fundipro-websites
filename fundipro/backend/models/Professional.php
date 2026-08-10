<?php
/**
 * Professional (Fundi) Model - FundiPro
 * Manages fundi profiles, verification, subscriptions
 */
class Professional {
    private $db;

    // Subscription pricing in KES
    const PLANS = [
        'free'     => ['price' => 0,    'jobs_per_month' => 3,    'label' => 'Free'],
        'pro'      => ['price' => 500,  'jobs_per_month' => null,  'label' => 'Pro'],
        'business' => ['price' => 1200, 'jobs_per_month' => null,  'label' => 'Business'],
    ];

    public function __construct($database) {
        $this->db = $database;
    }

    public function create($userId, $data) {
        $sql = "INSERT INTO professionals
                (user_id, bio, years_experience, hourly_rate, service_areas, trades, languages, subscription_tier)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'free')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $userId,
            $data['bio'] ?? null,
            $data['years_experience'] ?? 0,
            $data['hourly_rate'] ?? null,
            json_encode($data['service_areas'] ?? []),
            json_encode($data['trades'] ?? []),
            json_encode($data['languages'] ?? ['English', 'Kiswahili']),
        ]);
    }

    public function getByUserId($userId) {
        $sql = "SELECT p.*, u.first_name, u.last_name, u.phone, u.email
                FROM professionals p
                JOIN users u ON p.user_id = u.id
                WHERE p.user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function search($filters) {
        $sql = "SELECT p.*, u.first_name, u.last_name
                FROM professionals p
                JOIN users u ON p.user_id = u.id
                WHERE p.verification_status = 'verified'
                AND p.subscription_tier != 'free'";
        $params = [];

        if (!empty($filters['trade'])) {
            $sql .= " AND JSON_CONTAINS(p.trades, ?)";
            $params[] = json_encode($filters['trade']);
        }
        if (!empty($filters['county_id'])) {
            $sql .= " AND JSON_CONTAINS(p.service_areas, ?)";
            $params[] = json_encode((int)$filters['county_id']);
        }
        $sql .= " ORDER BY p.rating DESC, p.total_jobs DESC LIMIT 20";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateRating($id) {
        $sql = "UPDATE professionals p
                SET p.rating = (SELECT AVG(r.rating) FROM reviews r WHERE r.professional_id = p.id),
                    p.total_reviews = (SELECT COUNT(*) FROM reviews r WHERE r.professional_id = p.id)
                WHERE p.id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function canTakeJob($id) {
        $pro = $this->db->prepare("SELECT subscription_tier, total_jobs, subscription_expires FROM professionals WHERE id = ?");
        $pro->execute([$id]);
        $data = $pro->fetch(PDO::FETCH_ASSOC);

        if ($data['subscription_tier'] !== 'free') return true;

        // Free tier: max 3 jobs per month
        $sql = "SELECT COUNT(*) as count FROM jobs j
                JOIN quotes q ON q.job_id = j.id
                WHERE q.professional_id = ? AND q.status = 'accepted'
                AND MONTH(j.created_at) = MONTH(NOW())
                AND YEAR(j.created_at) = YEAR(NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] < self::PLANS['free']['jobs_per_month'];
    }
}
