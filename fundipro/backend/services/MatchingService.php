<?php
/**
 * Matching Service - FundiPro
 * Matches new jobs to verified professionals and sends SMS alerts
 */
class MatchingService {
    private $db;
    private $sms;

    public function __construct($database) {
        $this->db  = $database;
        $this->sms = new SMSService();
    }

    public function matchJobToProfessionals($jobId) {
        $job = $this->getJob($jobId);
        if (!$job) return [];

        // Find matching verified professionals
        $sql = "SELECT p.id, u.phone, u.first_name,
                       p.subscription_tier, p.rating
                FROM professionals p
                JOIN users u ON p.user_id = u.id
                WHERE p.verification_status = 'verified'
                AND JSON_CONTAINS(p.trades, JSON_QUOTE(?))
                AND JSON_CONTAINS(p.service_areas, CAST(? AS JSON))
                AND p.subscription_tier != 'free'
                ORDER BY p.rating DESC
                LIMIT 10";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$job['service_name'], $job['county_id']]);
        $professionals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Notify each matched professional via SMS
        foreach ($professionals as $pro) {
            $this->sms->sendJobAlert(
                $pro['phone'],
                $job['title'],
                $job['county_name']
            );
        }

        return $professionals;
    }

    public function scoreMatch($professional, $job) {
        $score = 0;
        $score += $professional['rating'] * 20;       // Max 100
        $score += min($professional['total_jobs'], 50); // Max 50
        if ($professional['subscription_tier'] === 'business') $score += 30;
        if ($professional['subscription_tier'] === 'pro')      $score += 15;
        return $score;
    }

    private function getJob($jobId) {
        $sql = "SELECT j.*, s.name AS service_name, c.name AS county_name
                FROM jobs j
                JOIN services s ON j.service_id = s.id
                JOIN counties c ON j.county_id = c.id
                WHERE j.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$jobId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
