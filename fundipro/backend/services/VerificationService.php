<?php
/**
 * Verification Service - FundiPro
 * Professional identity verification workflow
 * National ID check + selfie match for Kenya
 */
class VerificationService {
    private $db;

    public function __construct($db) { $this->db = $db; }

    public function submitVerification($professionalId, $data) {
        $required = ['national_id', 'id_photo_url', 'selfie_url'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        $sql = "UPDATE professionals
                SET national_id = ?, id_photo_url = ?, selfie_url = ?,
                    certificate_urls = ?, verification_status = 'pending'
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['national_id'],
            $data['id_photo_url'],
            $data['selfie_url'],
            json_encode($data['certificate_urls'] ?? []),
            $professionalId,
        ]);

        // Create admin review task
        $this->db->prepare(
            "INSERT INTO verification_queue (professional_id, submitted_at) VALUES (?, NOW())"
        )->execute([$professionalId]);

        return true;
    }

    public function approve($professionalId, $adminId) {
        $this->db->prepare(
            "UPDATE professionals SET verification_status = 'verified' WHERE id = ?"
        )->execute([$professionalId]);
        $this->logAction($professionalId, $adminId, 'approved');
    }

    public function reject($professionalId, $adminId, $reason) {
        $this->db->prepare(
            "UPDATE professionals SET verification_status = 'rejected' WHERE id = ?"
        )->execute([$professionalId]);
        $this->logAction($professionalId, $adminId, 'rejected', $reason);
    }

    private function logAction($professionalId, $adminId, $action, $reason = null) {
        $this->db->prepare(
            "INSERT INTO verification_logs (professional_id, admin_id, action, reason, created_at)
             VALUES (?, ?, ?, ?, NOW())"
        )->execute([$professionalId, $adminId, $action, $reason]);
    }
}
