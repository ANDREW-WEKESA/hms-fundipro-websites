<?php
/**
 * Job Model - FundiPro
 * Customer job postings with Kenya location support
 */
class Job {
    private $db;

    const STATUSES = ['open', 'quoted', 'booked', 'in_progress', 'completed', 'cancelled'];

    public function __construct($database) {
        $this->db = $database;
    }

    public function create($data) {
        $sql = "INSERT INTO jobs
                (customer_id, service_id, title, description, county_id, town, estate,
                 budget_min, budget_max, preferred_date, photos, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'open')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['customer_id'],
            $data['service_id'],
            $data['title'],
            $data['description'],
            $data['county_id'],
            $data['town'] ?? null,
            $data['estate'] ?? null,
            $data['budget_min'] ?? null,
            $data['budget_max'] ?? null,
            $data['preferred_date'] ?? null,
            json_encode($data['photos'] ?? []),
        ]);
        return $this->db->lastInsertId();
    }

    public function getById($id) {
        $sql = "SELECT j.*, s.name AS service_name, c.name AS county_name,
                       u.first_name, u.last_name, u.phone
                FROM jobs j
                JOIN services s ON j.service_id = s.id
                JOIN counties c ON j.county_id = c.id
                JOIN users u ON j.customer_id = u.id
                WHERE j.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getOpenByService($serviceId, $countyId = null) {
        $sql = "SELECT j.*, c.name AS county_name, s.name AS service_name
                FROM jobs j
                JOIN counties c ON j.county_id = c.id
                JOIN services s ON j.service_id = s.id
                WHERE j.status = 'open' AND j.service_id = ?";
        $params = [$serviceId];
        if ($countyId) {
            $sql .= " AND j.county_id = ?";
            $params[] = $countyId;
        }
        $sql .= " ORDER BY j.created_at DESC LIMIT 50";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status) {
        if (!in_array($status, self::STATUSES)) {
            throw new Exception("Invalid job status: $status");
        }
        $stmt = $this->db->prepare("UPDATE jobs SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
}
