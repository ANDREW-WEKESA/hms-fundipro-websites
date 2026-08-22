<?php
/**
 * Search Service - FundiPro
 * Location-based professional search for Kenya
 */
class SearchService {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function searchProfessionals($filters) {
        $sql = "SELECT p.*, u.first_name, u.last_name, u.phone,
                       c.name AS county_name, s.name AS service_name
                FROM professionals p
                JOIN users u ON p.user_id = u.id
                LEFT JOIN counties c ON JSON_CONTAINS(p.service_areas, CAST(c.id AS JSON))
                LEFT JOIN services s ON JSON_CONTAINS(p.trades, JSON_QUOTE(s.name))
                WHERE p.verification_status = 'verified' AND u.is_active = 1";
        $params = [];

        if (!empty($filters['service'])) {
            $sql .= " AND JSON_CONTAINS(p.trades, JSON_QUOTE(?))";
            $params[] = $filters['service'];
        }
        if (!empty($filters['county_id'])) {
            $sql .= " AND JSON_CONTAINS(p.service_areas, CAST(? AS JSON))";
            $params[] = (int)$filters['county_id'];
        }
        if (!empty($filters['min_rating'])) {
            $sql .= " AND p.rating >= ?";
            $params[] = $filters['min_rating'];
        }
        if (!empty($filters['verified_only'])) {
            $sql .= " AND p.verification_status = 'verified'";
        }

        // Sort options
        $sort = match($filters['sort'] ?? 'rating') {
            'price_low'  => 'p.hourly_rate ASC',
            'price_high' => 'p.hourly_rate DESC',
            'jobs'       => 'p.total_jobs DESC',
            default      => 'p.rating DESC',
        };
        $sql .= " ORDER BY $sort LIMIT 20";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPopularServices($countyId = null) {
        $sql = "SELECT s.id, s.name, s.category, COUNT(j.id) AS job_count
                FROM services s
                LEFT JOIN jobs j ON j.service_id = s.id";
        $params = [];
        if ($countyId) {
            $sql .= " AND j.county_id = ?";
            $params[] = $countyId;
        }
        $sql .= " WHERE s.is_active = 1 GROUP BY s.id ORDER BY job_count DESC LIMIT 8";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
