<?php
/**
 * Portfolio Model - FundiPro
 * Fundi work showcase with before/after photos
 */
class Portfolio {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function create($data) {
        $sql = "INSERT INTO portfolios
                (professional_id, title, description, before_photos, after_photos, service_id, completed_date)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['professional_id'],
            $data['title'],
            $data['description'] ?? null,
            json_encode($data['before_photos'] ?? []),
            json_encode($data['after_photos'] ?? []),
            $data['service_id'] ?? null,
            $data['completed_date'] ?? date('Y-m-d'),
        ]);
        return $this->db->lastInsertId();
    }

    public function getByProfessional($professionalId) {
        $sql = "SELECT p.*, s.name AS service_name
                FROM portfolios p
                LEFT JOIN services s ON p.service_id = s.id
                WHERE p.professional_id = ?
                ORDER BY p.completed_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$professionalId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
