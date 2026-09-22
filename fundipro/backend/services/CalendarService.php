<?php
/**
 * Calendar Service - FundiPro
 * Professional availability and scheduling in Africa/Nairobi timezone
 */
class CalendarService {
    private $db;
    const TIMEZONE = 'Africa/Nairobi';

    public function __construct($db) { $this->db = $db; }

    public function setAvailability($professionalId, $schedule) {
        // $schedule: ['monday' => ['09:00','17:00'], 'tuesday' => ['09:00','17:00'], ...]
        $stmt = $this->db->prepare(
            "UPDATE professionals SET availability = ? WHERE id = ?"
        );
        return $stmt->execute([json_encode($schedule), $professionalId]);
    }

    public function isAvailable($professionalId, $date, $time) {
        $tz  = new DateTimeZone(self::TIMEZONE);
        $dt  = new DateTime("$date $time", $tz);
        $day = strtolower($dt->format('l'));

        $stmt = $this->db->prepare("SELECT availability FROM professionals WHERE id = ?");
        $stmt->execute([$professionalId]);
        $pro  = $stmt->fetch(PDO::FETCH_ASSOC);
        $avail = json_decode($pro['availability'] ?? '{}', true);

        if (!isset($avail[$day])) return false;
        [$start, $end] = $avail[$day];
        $jobTime = $dt->format('H:i');
        return $jobTime >= $start && $jobTime <= $end;
    }

    public function getBookedSlots($professionalId, $month) {
        $sql = "SELECT scheduled_date, scheduled_time FROM bookings
                WHERE professional_id = ? AND status IN ('confirmed','in_progress')
                AND DATE_FORMAT(scheduled_date, '%Y-%m') = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$professionalId, $month]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function blockDate($professionalId, $date, $reason = 'blocked') {
        $stmt = $this->db->prepare(
            "INSERT INTO blocked_dates (professional_id, blocked_date, reason) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE reason = VALUES(reason)"
        );
        return $stmt->execute([$professionalId, $date, $reason]);
    }
}
